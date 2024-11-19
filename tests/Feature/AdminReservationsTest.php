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

class AdminReservationsTest extends TestCase
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
        $user = User::factory()->create();
        $this->actingAs($user);


        $expected = [];
        foreach ($resa as $r) {
            array_push($expected,[
                    "nom" => $r->nom,
                    "nomoutil"=> $outil->nom,
                    "reference" => $r->reference, 
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

        $response = $this->get('/api/adminreservations');
        //$response->dump();
        $response->assertStatus(200);        
        $response->assertJson(['status' => true, "data" => $expected]);
    }


    /**
     * Tester le retour des resa
     * 
     */
    /*
    public function test_get_reservations_not_admin() : void
    {
        
        $outil = Outils::factory()->create();
        $resa = Reservations::factory()->count(2)->create(['debut'=> '2024-10-24', 'fin'=>'2024-10-30']);

        $response = $this->get('/api/adminreservations');
        //$response->dump();
        $response->assertStatus(302);        
        $response->assertJson(['status' => true, "data" => ""]);
        
        //Probleme ca genere une erreur 501
    }
        */
}