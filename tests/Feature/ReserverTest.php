<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Outils;
use App\Models\Reservations;
use App\Models\JournalReservations;
use App\Models\User;
use App\Models\Helloasso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

// Atention pour eviter l'erreur : Vite manifest not found at
// Il faut executer npm run build pour construire le fichier 
// manifest avant de lancer les tests.

//Test de la fonctionalite reservation

class ReserverTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Tester une reservation d'un utilisateur qui paie en cash
     * 
     */
    public function test_je_reserve_et_je_paie_cash() : void
    {
        $outil = Outils::factory()->create();
        $data = [ "nom" => "Jo l'embrouille" , 
        "telephone" => "01020304",
        'email' => 'jo@truc.net',
        'outil_id' => 1,
        'debut' => '2024-10-3',
        'fin' => '2024-10-10'
      ];


      $response = $this->postJson('/api/reservations',$data);
      $response->assertStatus(201);        
      $response->assertJson(['status' => true, "data" => $data]);

      $resa = Reservations::find($response->json()["data"]["id"]);

      $this->assertEquals(Reservations::STATE_RESERVE,$resa->state);
      $this->assertEquals(Reservations::PAIEMENT_STATE_NON_PAYE,$resa->paiement_state);

      //Paiement cash
      $response = $this->put("/api/cash/$resa->id");
      $response->assertStatus(202);        
      $response->assertJson(['status' => true, "data" => $data]);

      $resa = Reservations::find($response->json()["data"]["id"]);

      $this->assertEquals(Reservations::STATE_PAIEMENT,$resa->state);
      $this->assertEquals(Reservations::PAIEMENT_STATE_A_PAYER,$resa->paiement_state);

      //Verifie pour confirmer
      $response = $this->get("/api/checkpaiement/$resa->id");
      $response->assertStatus(200);        
      $response->assertJson(['status' => true, "data" => $data]);

      $resa = Reservations::find($response->json()["data"]["id"]);

      $this->assertEquals(Reservations::STATE_CONFIRME,$resa->state);
      $this->assertEquals(Reservations::PAIEMENT_STATE_A_PAYER,$resa->paiement_state);

      //Verifions que le mail a été envoyé

    }

    /**
     * Tester une reservation d'un utilisateur qui paie en cash
     * 
     */
    public function test_je_reserve_et_je_paie_sur_helloasso() : void
    {
        $outil = Outils::factory()->create();
        $data = [ "nom" => "Jo l'embrouille" , 
        "telephone" => "01020304",
        'email' => 'jo@truc.net',
        'outil_id' => 1,
        'debut' => '2024-10-3',
        'fin' => '2024-10-10'
      ];


      $response = $this->postJson('/api/reservations',$data);
      $response->assertStatus(201);        
      $response->assertJson(['status' => true, "data" => $data]);

      $resa = Reservations::find($response->json()["data"]["id"]);

      $this->assertEquals(Reservations::STATE_RESERVE,$resa->state);
      $this->assertEquals(Reservations::PAIEMENT_STATE_NON_PAYE,$resa->paiement_state);

      //Paiement helloasso
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

        $expected = ["id"=>31270,'redirectUrl' => "https://redirectionurl.com"];

        Http::fake([
            'https://api.helloasso-sandbox.com/v5/organizations/labobinette/checkout-intents' => Http::response($expected, 200)
            ]);


      $response = $this->get("/api/encaissement/$resa->id");
      $response->assertStatus(200);        
      $response->assertJson(['status' => true, "data" => $expected]);

      $resa = Reservations::find($resa->id);

      $this->assertEquals(Reservations::STATE_PAIEMENT,$resa->state);
      $this->assertEquals(Reservations::PAIEMENT_STATE_HA_ENCOURS,$resa->paiement_state);

      //Verifie pour confirmer
      $str= '{"order":
      {"payer": {"email":"lhechris@gmail.com","country":"FRA","firstName":"Rafa","lastName":"Nadal"},
       "items":[{"payments":[{"id":19609,"shareAmount":200}],"name":"Location outil Nadal","priceCategory":"Fixed","qrCode":"MjkxOTM6NjM4NjUxMjcwNjkyOTcyNTEw","id":29193,"amount":200,"type":"Payment","state":"Processed"}],
       "payments":[{"items":[{"id":29193,"shareAmount":200,"shareItemAmount":200}],"cashOutState":"MoneyIn","paymentReceiptUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/paiement-attestation/29193","id":19609,"amount":200,"date":"2024-10-21T17:04:55.3020839+02:00","paymentMeans":"Card","installmentNumber":1,"state":"Authorized","meta":{"createdAt":"2024-10-21T17:04:29.297251+02:00","updatedAt":"2024-10-21T17:04:55.3533333+02:00"},"refundOperations":[]}],"amount":{"total":200,"vat":0,"discount":0},"id":29193,"date":"2024-10-21T17:04:55.3020839+02:00","formSlug":"default","formType":"Checkout","organizationName":"labobinette","organizationSlug":"labobinette","organizationType":"Association1901Rig","organizationIsUnderColucheLaw":false,"checkoutIntentId":30674,"meta":{"createdAt":"2024-10-21T17:04:29.297251+02:00","updatedAt":"2024-10-21T17:04:55.442711+02:00"},"isAnonymous":false,"isAmountHidden":false},
     "id":31270,
     "redirectUrl":"https://www.helloasso-sandbox.com/associations/labobinette/checkout/a5228b6e2bb74b11e0a108dcf1cc3516"}';

       $expected = json_decode($str,true);

        Http::fake([
            'https://api.helloasso-sandbox.com/v5/organizations/labobinette/checkout-intents/31270' => Http::response($expected, 200)
            ]);

      $response = $this->get("/api/checkpaiement/$resa->id");
      $response->assertStatus(200);        
      $response->assertJson(['status' => true, "data" => $data]);

      $resa = Reservations::find($response->json()["data"]["id"]);

      $this->assertEquals(Reservations::STATE_CONFIRME,$resa->state);
      $this->assertEquals(Reservations::PAIEMENT_STATE_HA_PAYE,$resa->paiement_state);

      //Verifions que le mail a été envoyé

    }

}



