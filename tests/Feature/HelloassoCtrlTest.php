<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Outils;
use App\Models\Reservations;
use App\Models\Helloasso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

// Atention pour eviter l'erreur : Vite manifest not found at
// Il faut executer npm run build pour construire le fichier 
// manifest avant de lancer les tests.

//Route::get('encaissement/{resa}', [HelloassoController::class,'encaissement']);
//Route::get('checkpaiement/{resa}', [HelloassoController::class,'checkPaiement']);
//Route::put('cash/{resa}', [HelloassoController::class,'cash']);

class HelloassoCtrlTest extends TestCase
{

    use RefreshDatabase;

  private function fake_token() {
      $ha = Helloasso::factory()->createMany([["nom" =>"Testaccess_token","valeur" => "REFABCDEF"],
                                              ["nom" =>"Testrefresh_token","valeur" => "ACCABCDEF"],
                                              ["nom" =>"Testclient_secret","valeur" => "KEY12345"],
                                              ["nom" =>"Testclient_id","valeur" => "CLID12345"],
      ]);

      Http::fake([
      'https://api.helloasso-sandbox.com/oauth2/token' => Http::response([
      'access_token' => "accGHIJK",
      'refresh_token' => 'refGHIJK',
      "expires_in" => 1800,
      "token_type" => "bearer"], 200)
      ]);
  }


    /**
     * Tester l'encaissement d'une reservation helloasso
     * 
     */
    public function test_encaissement_reserve_et_nonpaye() : void
    {
        $outil = Outils::factory()->create();
        $resa = Reservations::factory()->create([
                'debut'=> '2024-10-24', 
                'fin'=>'2024-10-30',
                'state'=> Reservations::STATE_RESERVE, 
                'paiement_state' => Reservations::PAIEMENT_STATE_NON_PAYE]);

        $this->fake_token();
        //Paiement helloasso
      
        $expected = ["id"=>31270,'redirectUrl' => "https://redirectionurl.com"];

        Http::fake([
            'https://api.helloasso-sandbox.com/v5/organizations/labobinette/checkout-intents' => Http::response($expected, 200)
            ]);


        $response = $this->get("/api/encaissement/$resa->reference");
        $response->assertStatus(200);        
        $response->assertJson(['status' => true, "data" => $expected]);

        $resa = Reservations::find($resa->id);

        $this->assertEquals(Reservations::STATE_PAIEMENT,$resa->state);
        $this->assertEquals(Reservations::PAIEMENT_STATE_HA_ENCOURS,$resa->paiement_state);
    }


    /**
     * Tester l'encaissement d'une reservation helloasso
     * 
     */
    public function test_encaissement_erreur_helloasso() : void
    {
        $outil = Outils::factory()->create();
        $resa = Reservations::factory()->create([
                'debut'=> '2024-10-24', 
                'fin'=>'2024-10-30',
                'state'=> Reservations::STATE_RESERVE, 
                'paiement_state' => Reservations::PAIEMENT_STATE_NON_PAYE]);

        $this->fake_token();
        
        //Paiement helloasso
        $erreurha = ["messsage" => "truc"];

        Http::fake([
            'https://api.helloasso-sandbox.com/v5/organizations/labobinette/checkout-intents' => Http::response($erreurha, 400)
            ]);


      $response = $this->get("/api/encaissement/$resa->reference");
      $response->assertStatus(200);        
      $response->assertJson(['status' => false, 'data' => "Erreur communication helloasso"]);

      $resa = Reservations::find($resa->id);

      //les status ne doivent pas bouger
      $this->assertEquals(Reservations::STATE_RESERVE,$resa->state);
      $this->assertEquals(Reservations::PAIEMENT_STATE_NON_PAYE,$resa->paiement_state);
    }



}



