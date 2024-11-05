<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Outils;
use App\Models\Reservations;
use App\Models\JournalReservations;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

// Atention pour eviter l'erreur : Vite manifest not found at
// Il faut executer npm run build pour construire le fichier 
// manifest avant de lancer les tests.

//Route::apiResource('reservations', ReservationController::class);

class ReservationsCtrlTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Tester le retour des resa
     * 
     */
    public function test_get_reservations() : void
    {
        $outil = Outils::factory()->create();
        $resa = Reservations::factory()->count(2)->create(['debut'=> '2024-10-24', 'fin'=>'2024-10-30']);

        $expected = [];
        foreach ($resa as $r) {
            array_push($expected,[
                    "nom" => $r->nom,
                    "nomoutil"=> $outil->nom, 
                    "email"=>$r->email, 
                    "telephone"=>$r->telephone,
                    "outil_id"=>$r->outil_id,
                    "debut" => $r->debut,
                    "fin" => $r->fin,
                    "paiement_state" => $r->paiement_state,
                    "state" => $r->state,
                    "commentaire" => $r->commentaire
                ]);
        }

        $response = $this->get('/api/reservations');
        //$response->dump();
        $response->assertStatus(200);        
        $response->assertJson(['status' => true, "data" => $expected]);
    }

    /**
     * Tester le retour des resa alors qu'il n'y en a pas
     * 
     */
    public function test_get_reservations_with_no_resa() : void
    {

        $expected = [];

        $response = $this->get('/api/reservations');
        $response->assertStatus(200);        
        $response->assertJson(['status' => true, "data" => $expected]);
    }
    
    /**
     * Tester une nouvelle resa
     * 
     */
    public function test_post_reservations() : void
    {

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
    }

    /**
     * Tester une mise à jour d'une resa
     * 
     */
    public function test_put_reservations_non_authentifie() : void
    {
        $resa = Reservations::factory()->count(2)->create(['debut'=> '2024-10-24', 'fin'=>'2024-10-30']);
        $user = User::factory()->create();
        
        $data = [ "nom" => "Jo l'embrouille" , 
                "telephone" => "01020304",
                'email' => 'jo@truc.net',
                'outil_id' => 1,
                'debut' => '2024-10-3',
                'fin' => '2024-10-10'
              ];

        $response = $this->putJson('/api/reservations/1',$data);
        //$response = $this->actingAs($user)->putJson('/api/reservations/1',$data);
        $response->assertStatus(401);        
        $response->assertJson(["status" => false, "message" => "Non authorisé", "errors" => []]);
    }


    /**
     * Tester une mise à jour d'une resa
     * on ne modifie que debut, fin et outil_id
     * 
     */
    public function test_put_reservations_authentifie() : void
    {
        $resa = Reservations::factory()->count(2)->create(['debut'=> '2024-10-24', 'fin'=>'2024-10-30']);
        $user = User::factory()->create();
        
        $data = [ "nom" => "Jo l'embrouille" , 
                "telephone" => "01020304",
                'email' => 'jo@truc.net',
                'outil_id' => 2,
                'debut' => '2024-10-3',
                'fin' => '2024-10-10'
              ];

        $expected = [ "nom" => $resa[0]->nom , 
                    "telephone" => $resa[0]->telephone,
                    'email' => $resa[0]->email,
                    'outil_id' => $data["outil_id"],
                    'debut' => $data["debut"],
                    'fin' => $data["fin"]
                ];

        $response = $this->actingAs($user)->putJson('/api/reservations/1',$data);
        $response->assertStatus(202);        
        $response->assertJson(["status" => true, "data" => $expected]);
    }

    /**
     * Tester une mise à jour d'une resa
     * on ne modifie que debut, fin et outil_id
     * 
     */
    public function test_put_reservations_non_admin() : void
    {
        $resa = Reservations::factory()->count(2)->create(['debut'=> '2024-10-24', 'fin'=>'2024-10-30']);
        $user = User::factory()->create(['role'=>"user"]);

        $data = [ "nom" => "Jo l'embrouille" , 
                "telephone" => "01020304",
                'email' => 'jo@truc.net',
                'outil_id' => 2,
                'debut' => '2024-10-3',
                'fin' => '2024-10-10'
              ];

        $response = $this->actingAs($user)->putJson('/api/reservations/1',$data);
        $response->assertStatus(401);        
        $response->assertJson(["status" => false, "message" => "Non authorisé", "errors" => []]);
    }

    /**
     * Tester la suppression d'une reservation
     * uniquement dans l'etat RESERVE     
     */
    public function test_annulation_reservations() : void
    {
        $resa = Reservations::factory()->create(["state" => Reservations::STATE_RESERVE]);

        $response = $this->delete('/api/reservations/1');
        $response->assertStatus(200);        
        $j=JournalReservations::find(1);
        $this->assertEquals(Reservations::STATE_ANNULE,$j->state);
        $this->assertNull(Reservations::find(1));
    }

    /**
     * Tester la suppression d'une reservation
     */
    public function test_annulation_reservations_state_paiement() : void
    {
        $resa = Reservations::factory()->create(["state" => Reservations::STATE_PAIEMENT]);

        $response = $this->delete('/api/reservations/1');
        $response->assertStatus(401);
        $response->assertJson(["status" => false, "message" => "Non authorisé"]);
        $this->assertNotNull(Reservations::find(1));
    }

    /**
     * Tester la suppression d'une reservation
     */
    public function test_annulation_reservations_state_annule() : void
    {
        $resa = Reservations::factory()->create(["state" => Reservations::STATE_ANNULE]);

        $response = $this->delete('/api/reservations/1');
        $response->assertStatus(401);
        $response->assertJson(["status" => false, "message" => "Non authorisé"]);
        $this->assertNotNull(Reservations::find(1));
    }

    /**
     * Tester la suppression d'une reservation
     */
    public function test_annulation_reservations_state_confirm() : void
    {
        $resa = Reservations::factory()->create(["state" => Reservations::STATE_CONFIRME]);

        $response = $this->delete('/api/reservations/1');
        $response->assertStatus(401);
        $response->assertJson(["status" => false, "message" => "Non authorisé"]);
        $this->assertNotNull(Reservations::find(1));
    }

}



