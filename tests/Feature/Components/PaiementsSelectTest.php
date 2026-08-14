<?php

use App\Models\Category;
use App\Models\Contract;
use App\Models\Feature;
use App\Models\Reservation;
use App\Models\Tool;
use App\Models\User;
use App\Services\SrvReservation; 
use Illuminate\Support\Carbon;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
| On fige la date pour que la génération des jeudis (mount()) et les
| règles de validation "after_or_equal:today" / "after:date_start"
| soient déterministes.
*/

beforeEach(function () {
    // Mardi 10/06/2025, pour avoir un point de départ stable.
    Carbon::setTestNow(Carbon::parse('2025-06-10 10:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});



/*
|--------------------------------------------------------------------------
| Accès / authentification
|--------------------------------------------------------------------------
*/

it("redirige vers la page de connexion si l'utilisateur n'est pas authentifié", function () {
    
    $user = makeUser();
    $tool = makeTool();
    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $user->id,
    ]);

    Livewire::test('paiements.select', ['ref' => $reservation->reference])
        ->assertRedirect(route('login'));
});

it("redirige vers la page tools.index si l'utilisateur authentifié n'est pas celui de la réservation", function () {
    $user1 = makeUser();
    $user2 = makeUser();
    $tool = makeTool();
    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $user1->id,
    ]);
    
    actingAs($user2);

    Livewire::test('paiements.select', ['ref' => $reservation->reference])
        ->assertRedirect(route('tools.index'));
});

it("affiche page 404 (NotFound) si la reservation n'existe pas", function () {
    $user = makeUser();

    actingAs($user)
        ->get(route('payments.select',['ref'=> 'blabla']))
        ->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Affichage
|--------------------------------------------------------------------------
*/
it("affiche les informations de paiement pour un utilisateur authentifié", function () {
    $user = makeUser();
    $tool = makeTool();
    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $user->id,
    ]);

    actingAs($user);

    $this->mock(SrvReservation::class, function ($mock) {
        $mock->shouldReceive('needToPay')->once()->andReturn(true);
    });

    Livewire::test('paiements.select', ['ref' => $reservation->reference])
        ->assertSee('Page Paiement de la réservation')
        ->assertSee($reservation->reference)
        ->assertSee('handleHA')
        ->assertSee('handleCash')
        ->assertSee('handleCancel');
});

it("affiche les informations de paiement pour un utilisateur qui a payé", function () {
    $user = makeUser();
    $tool = makeTool();
    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $user->id,
        'state'   => Reservation::STATE_CONFIRMED,
    ]);

    actingAs($user);

    $this->mock(SrvReservation::class, function ($mock) {
        $mock->shouldReceive('needToPay')->once()->andReturn(false);
    });

    //dump(DB::select("select * from reservations"));

    Livewire::test('paiements.select', ['ref' => $reservation->reference])
        ->assertSee('Confirmation de votre réservation')
        ->assertSee($reservation->reference)
        ->assertDontSee('handleHA')
        ->assertDontSee('handleCash')
        ->assertDontSee('handleCancel');
});