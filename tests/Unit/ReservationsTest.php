<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use App\Models\Outils;
use App\Models\Reservations;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Mockery;
use Mockery\MockInterface;
use App\Models\ConfirmResa;
use Illuminate\Support\Str;

class ReservationsTest extends TestCase
{

    use RefreshDatabase;


    /**
     * Verifie que l'outil en parametre peut être reservé
     */
    public function test_outil_peut_etre_reserve(): void
    {
        $outil = Outils::factory()->create(['nombre'=> 3]);
        $resa = Reservations::factory()->count(2)->create(['debut'=> '2024-10-24', 'fin'=>'2024-10-30']);        

        $this->assertTrue(Reservations::est_possible(1,'2024-10-24','2024-10-30'));
    }

    /**
     * Verifie que l'outil en parametre peut être reservé
     */
    public function test_outil_peut_etre_reserve_sur_une_autre_plage(): void
    {
        $outil = Outils::factory()->create(['nombre'=> 3]);
        $resa = Reservations::factory()->count(3)->create(['debut'=> '2024-10-24', 'fin'=>'2024-10-30']);        

        $this->assertTrue(Reservations::est_possible(1,'2024-11-01','2024-11-7'));
    }

    /**
     * Verifie que l'outil en parametre peut être reservé
     */
    public function test_reservation_impossible_pas_de_disponibilite(): void
    {
        $outil = Outils::factory()->create(['nombre'=> 3]);
        $resa = Reservations::factory()->count(3)->create(['debut'=> '2024-10-24', 'fin'=>'2024-10-30']);        

        $this->assertFalse(Reservations::est_possible(1,'2024-10-24','2024-10-30'));
    }

    /**
     * Test que reference est bien renseigne a la creation
     */
    public function test_reference_est_genere_a_la_creation(): void
    {
        $outil = Outils::factory()->create();
        $resa = Reservations::create(['nom'=> 'machin','email'=> 'bidule@truc.com', 'telephone'=> '0102030405','outil_id'=> 1, 'debut'=> '2024-10-24', 'fin'=>'2024-10-30']);        

        $expected = Reservations::find(1);

        $this->assertEquals('LBO'.date('ym'),substr($expected->reference,0,7));        
    }


}