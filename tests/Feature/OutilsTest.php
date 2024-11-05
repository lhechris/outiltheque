<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Outils;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Atention pour eviter l'erreur : Vite manifest not found at
// Il faut executer npm run build pour construire le fichier 
// manifest avant de lancer les tests.

class OutilsTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertTrue(true);
    }

    /**
     * Tester le retour des outils
     * Route::get('outils', [OutilsController::class,'index']);
     */
    public function test_get_outils() : void
    {
        $outil = Outils::factory()->create();
        $expected = [["nom" => $outil->nom,
                     "description" => $outil->description,
                     "prix" => $outil->prix,
                     "duree" => $outil->duree,
                     "nombre" => $outil->nombre,
                     "categorie_id" => $outil->categorie_id,
                     "file_id" => 0,
                     "file2_id" => 0,
                     "conseil" => $outil->conseil,
                     "precaution" => $outil->precaution,
                     "created_at" => $outil->created_at->format("Y-m-d\TH:i:s.up"),
                     "updated_at" => $outil->updated_at->format("Y-m-d\TH:i:s.up"),
                     "categorie" => null,
                     "file_path" => null
                    ]];


        $response = $this->get('/api/outils');
        //$response->dump();
        $response->assertStatus(200);        
        $response->assertJson(['status' => true, "data" => $expected]);

    }

/*    
    Route::get('outilsbycat/{categorie}', [OutilsController::class,'indexbycategorie']);
    Route::get('outils/{outil}', [OutilsController::class,'show']);
    Route::post('outils', [OutilsController::class,'store'])->middleware('auth:sanctum','role:admin');
    Route::put('outils/{outil}', [OutilsController::class,'update'])->middleware('auth:sanctum','role:admin');
    Route::delete('outils/{outil}', [OutilsController::class,'destroy'])->middleware('auth:sanctum','role:admin');
*/

    
}
