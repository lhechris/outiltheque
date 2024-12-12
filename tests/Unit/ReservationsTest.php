<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use App\Models\Outils;
use App\Models\Reservations;
use App\Models\JournalReservations;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Mockery;
use Mockery\MockInterface;
use App\Models\ConfirmResa;
use Illuminate\Support\Str;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

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

    /**
     * Test la liste des reservations en cours
     */
    public function test_liste_resa_semaine() : void
    {
        $now = CarbonImmutable::now();

        //On crée 5 enregistrements :
        //   - jeudi de la semaine prochaine => rejeté
        //   - jeudi => ok
        //   - dimanche précédent => rejeté
        //   - le premier jour de la semaine (lundi) => ok
        //   - le dernier jour de la semaine (dimanche) => ok
        $resa = Reservations::factory()
                                ->count(5)
                                ->sequence(["debut"=> $now->StartOfWeek()->addDays(10)->format('Y-m-d')],
                                           ["debut"=> $now->StartOfWeek()->addDays(3)->format('Y-m-d')],
                                           ["debut"=> $now->StartOfWeek()->subDays(1)->format('Y-m-d')],
                                           ["debut"=> $now->StartOfWeek()->format('Y-m-d')],
                                           ["debut"=> $now->EndOfWeek()->format('Y-m-d')])
                                ->create();  
       
        /*foreach($resa as $r) {
            echo $r['id']." debut ".$r['debut']."\n";
        }*/

        $response = Reservations::listeResaSemaine();

        $this->assertCount(3,$response);
        $this->assertEquals(2,$response[0]->id);
        $this->assertEquals(4,$response[1]->id);
        $this->assertEquals(5,$response[2]->id);
    }

    /**
     * Test la liste des reservations en retour cette semaine
     */
    public function test_liste_retour_semaine() : void
    {
        $now = CarbonImmutable::now();

        //On crée 5 enregistrements :
        //   - mercredi de la semaine prochaine => rejeté
        //   - mercredi => ok
        //   - dimanche précédent => rejeté
        //   - le premier jour de la semaine (lundi) => ok
        //   - le dernier jour de la semaine (dimanche) => ok
        $resa = Reservations::factory()
                                ->count(5)
                                ->sequence(["fin"=> $now->StartOfWeek()->addDays(9)->format('Y-m-d')],
                                           ["fin"=> $now->StartOfWeek()->addDays(2)->format('Y-m-d')],
                                           ["fin"=> $now->StartOfWeek()->subDays(1)->format('Y-m-d')],
                                           ["fin"=> $now->StartOfWeek()->format('Y-m-d')],
                                           ["fin"=> $now->EndOfWeek()->format('Y-m-d')])
                                ->create();  
       
    /*    foreach($resa as $r) {
            echo $r['id']." fin ".$r['fin']."\n";
        }*/

        $response = Reservations::listeRetourSemaine();

        $this->assertCount(3,$response);
        $this->assertEquals(2,$response[0]->id);
        $this->assertEquals(4,$response[1]->id);
        $this->assertEquals(5,$response[2]->id);
    }


    /**
     * Test la liste des reservations en retour cette semaine
     */
    public function test_purge_reservations() : void
    {
        $now = CarbonImmutable::now();

        //On crée 5 enregistrements :
        $resa = Reservations::factory()
                                ->count(5)
                                ->sequence(["updated_at"=> $now->subMinutes(30)->format('Y-m-d H:i:s')],
                                           ["updated_at"=> $now->format('Y-m-d H:i:s')],
                                           ["updated_at"=> $now->subMinutes(16)->format('Y-m-d H:i:s')],
                                           ["updated_at"=> $now->subMinutes(14)->format('Y-m-d H:i:s')],
                                           ["updated_at"=> $now->subMinutes(30)->format('Y-m-d H:i:s'),"state" => Reservations::STATE_CONFIRME],
                                           )
                                ->create();  
       
    /*    foreach($resa as $r) {
            echo $r['id']." fin ".$r['fin']."\n";
        }*/

        Reservations::purge();

        $resa = Reservations::get();

        $this->assertCount(3,$resa);
        $this->assertEquals(2,$resa[0]->id);
        $this->assertEquals(4,$resa[1]->id);
        $this->assertEquals(5,$resa[2]->id);
    }

    /**
     * Test la liste des reservations en retour cette semaine
     */
    public function test_historise_reservations() : void
    {

        //On fixe la date à un jeudi
        $fakeDate = Carbon::create(2024, 12, 12, 12);
        Carbon::setTestNow($fakeDate);

        //On crée 5 enregistrements :
        //   - mercredi de la semaine prochaine
        //   - mercredi 
        //   - dimanche précédent 
        //   - le premier jour de la semaine (lundi) 
        //   - le dernier jour de la semaine (dimanche) 
        $resa = Reservations::factory()
                                ->count(5)
                                ->sequence(["fin" => "2024-12-19"],
                                           ["fin" => "2024-12-11"],
                                           ["fin" => "2024-12-08"],
                                           ["fin" => "2024-12-09"],
                                           ["fin" => "2024-12-15"])
                                ->create();  
       
        Reservations::historise();

        $resa = Reservations::get();

        //On verifie qu'il y a 3 reservations historisées et 2 qui n'ont pas bougées.
        $this->assertCount(2,$resa);
        $this->assertEquals('2024-12-19',$resa[0]->fin);
        $this->assertEquals('2024-12-15',$resa[1]->fin);

        $this->assertCount(3,JournalReservations::get());

        Carbon::setTestNow();

    }

}