<?php

use App\Models\Category;
use App\Models\Contract;
use App\Models\Reservation;
use App\Models\Tool;
use App\Models\User;
use App\Services\SrvReservation;
use App\Mail\ConfirmResa;
use App\Mail\NewResaForAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

/*
|--------------------------------------------------------------------------
| Hypothèses (cf. commentaires en tête de SrvReservation.php)
|--------------------------------------------------------------------------
| - User::contracts() : belongsToMany(Contract::class)->withPivot('payment_state')
| - Table pivot : contract_user, colonne payment_state avec valeurs 'paid' / 'unpaid'
| - Contract::maxpermonth (existant) et Contract::maxperyear (à ajouter si besoin)
| - Factories : User::factory(), Tool::factory(), Category::factory(), Contract::factory()
*/

uses( RefreshDatabase::class);


function givePaidContract(User $user, Contract $contract, string $state = Reservation::PAYMENT_STATE_PAYED): void
{
    $user->contracts()->attach($contract->id, ['payment_state' => $state]);
}

beforeEach(function () {
    Mail::fake();
    config(['mail.responsable_resa' => 'admin@club.test']);
    putenv('MAIL_RESPONSABLE_RESA=admin@club.test');
});

/*
|--------------------------------------------------------------------------
| Disponibilité / création de base
|--------------------------------------------------------------------------
*/

it('crée une réservation quand le matériel est disponible', function () {
    $tool    = makeTool(number: 2);
    $user    = makeUser();
    $service = new SrvReservation();

    $result = $service->create($user, $tool, '2026-08-13', '2026-08-19', 'unique', 'RAS');

    expect($result)->toBeTrue();
    $this->assertDatabaseCount('reservations', 1);

    $reservation = Reservation::first();

    expect($reservation->tool_id)->toBe($tool->id)
        ->and($reservation->state)->toBe(Reservation::STATE_RESERVED)
        ->and($reservation->payment_state)->toBe(Reservation::PAYMENT_STATE_UNPAID)
        ->and($service->getMessage())->toBe('Réservation effectuée avec succès.');
});

it("ne crée pas de réservation quand le matériel n'a plus de disponibilité", function () {
    $tool       = makeTool(number: 1);
    $firstUser  = makeUser();
    $secondUser = makeUser();
    $service    = new SrvReservation();

    $service->create($firstUser, $tool, '2026-08-13', '2026-08-19', 'unpaid', null);
    $this->assertDatabaseCount('reservations', 1);

    $result = $service->create($secondUser, $tool, '2026-08-13', '2026-08-19', 'unpaid', null);

    expect($result)->toBeFalse();
    $this->assertDatabaseCount('reservations', 1);
    expect($service->getMessage())->toBe("Ce matériel n'est plus disponible sur cette période.");
});

it("ignore les réservations annulées lors du calcul de disponibilité", function () {
    $tool       = makeTool(number: 1);
    $firstUser  = makeUser();
    $secondUser = makeUser();
    $service    = new SrvReservation();

    Reservation::factory()->create([
        'tool_id'    => $tool->id,
        'user_id'    => $firstUser->id,
        'date_start' => '2026-08-13',
        'date_end'   => '2026-08-19',
        'state'      => Reservation::STATE_CANCELLED,
    ]);

    $result = $service->create($secondUser, $tool, '2026-08-13', '2026-08-19', 'unpaid', null);

    expect($result)->toBeTrue();
    $this->assertDatabaseCount('reservations', 2);
});

/*
|--------------------------------------------------------------------------
| Forfait (pivot User <-> Contract)
|--------------------------------------------------------------------------
*/

it("crée une réservation forfait quand l'utilisateur a un contrat payé illimité", function () {
    $contract = Contract::factory()->create(['restriction' => 'none']); // illimité
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser();
    givePaidContract($user, $contract);

    $service = new SrvReservation();
    $result  = $service->create($user, $tool, '2026-08-13', '2026-08-19', 'forfait', null);

    expect($result)->toBeTrue();
    expect(Reservation::first()->payment_state)->toBe(Reservation::PAYMENT_STATE_FORFAIT);
    expect(Reservation::first()->state)->toBe(Reservation::STATE_CONFIRMED);

    Mail::assertSent(ConfirmResa::class, 1);
    Mail::assertSent(NewResaForAdmin::class, 1);

});

it("accepte une réservation forfait si l'utilisateur n'a pas de contrat", function () {
    $contract = Contract::factory()->create();
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser(); // pas de contrat souscrit

    $service = new SrvReservation();
    $result  = $service->create($user, $tool, '2026-08-13', '2026-08-19', 'forfait', null);

    expect($result)->toBeTrue();
    expect(Reservation::first()->payment_state)->toBe(Reservation::PAYMENT_STATE_FORFAIT);
    //$this->assertDatabaseCount('reservations', 0);
    //expect($service->getMessage())->toBe("Vous n'avez pas de forfait disponible pour ce matériel.");
    Mail::assertNothingSent();

});

it("accepte une réservation forfait si le contrat n'est pas payé", function () {
    $contract = Contract::factory()->create();
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser();
    givePaidContract($user, $contract, state: Reservation::PAYMENT_STATE_UNPAID);

    $service = new SrvReservation();
    $result  = $service->create($user, $tool, '2026-08-13', '2026-08-19', 'forfait', null);

    expect($result)->toBeTrue();
    expect(Reservation::first()->payment_state)->toBe(Reservation::PAYMENT_STATE_FORFAIT);
    //$this->assertDatabaseCount('reservations', 0);
    Mail::assertNothingSent();
});

it("refuse une réservation forfait quand le quota mensuel est atteint", function () {
    $contract = Contract::factory()->create(['restriction' => '1 par mois']); // 1 résa forfait max / mois
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser();
    givePaidContract($user, $contract);

    $service = new SrvReservation();

    // 1ère résa forfait du mois : OK
    $first = $service->create($user, $tool, '2026-08-06', '2026-08-12', 'forfait', null);
    expect($first)->toBeTrue();

    // 2ème résa forfait le même mois : quota atteint => refusée
    $second = $service->create($user, $tool, '2026-08-13', '2026-08-19', 'forfait', null);

    expect($second)->toBeFalse();
    $this->assertDatabaseCount('reservations', 1);
    expect($service->getMessage())->toBe("Vous avez dépasser le quota de réservation (1 par mois).");
});

it("accepte une réservation forfait quand le quota mensuel est de nouveau possible", function () {
    $contract = Contract::factory()->create(['restriction' => '2 par mois']); // 1 résa forfait max / mois
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser();
    givePaidContract($user, $contract);

    $service = new SrvReservation();

    // 1ère résa forfait du mois : OK
    $first = $service->create($user, $tool, '2026-08-06', '2026-08-12', 'forfait', null);
    expect($first)->toBeTrue();

    // 2ème résa forfait le même mois : OK
    $second = $service->create($user, $tool, '2026-08-13', '2026-08-19', 'forfait', null);
    expect($second)->toBeTrue();
    $this->assertDatabaseCount('reservations', 2);

    // 3ème résa forfait le mois suivant: OK
    $third = $service->create($user, $tool, '2026-09-10', '2026-09-16', 'forfait', null);
    expect($third)->toBeTrue();
    $this->assertDatabaseCount('reservations', 3);

});

it("refuse une réservation forfait quand le quota glissant est atteint", function () {
    $contract = Contract::factory()->create(['restriction' => '1 pendant 60 jours']); 
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser();
    givePaidContract($user, $contract);

    $service = new SrvReservation();

    // 1ère résa forfait du mois : OK
    $first = $service->create($user, $tool, '2026-08-06', '2026-08-12', 'forfait', null);
    expect($first)->toBeTrue();

    // 2ème résa forfait dans les 60 jours : quota atteint => refusée
    $second = $service->create($user, $tool, '2026-10-01', '2026-10-08', 'forfait', null);
    expect($second)->toBeFalse();

    $this->assertDatabaseCount('reservations', 1);
    expect($service->getMessage())->toBe("Vous avez dépasser le quota de réservation (1 pendant 60 jours).");
});

it("refuse une réservation forfait quand le quota annuel est atteint", function () {
    $contract = Contract::factory()->create(['restriction' => '1 par an']);
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser();
    givePaidContract($user, $contract);

    $service = new SrvReservation();

    $first = $service->create($user, $tool, '2026-01-08', '2026-01-14', 'forfait', null);
    expect($first)->toBeTrue();

    // Autre période de l'année, quota annuel déjà atteint
    $second = $service->create($user, $tool, '2026-08-13', '2026-08-19', 'forfait', null);

    expect($second)->toBeFalse();
    $this->assertDatabaseCount('reservations', 1);
});

it("ne compte pas les réservations forfait annulées dans le quota", function () {
    $contract = Contract::factory()->create(['restriction' => '1 par mois']);
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser();
    givePaidContract($user, $contract);

    Reservation::factory()->create([
        'tool_id'       => $tool->id,
        'user_id'       => $user->id,
        'date_start'    => now()->startOfMonth()->addDays(2),
        'date_end'      => now()->startOfMonth()->addDays(5),
        'state'         => Reservation::STATE_CANCELLED,
        'payment_state' => Reservation::PAYMENT_STATE_FORFAIT,
    ]);

    $service = new SrvReservation();
    $result  = $service->create(
        $user,
        $tool,
        now()->startOfMonth()->addDays(10)->toDateString(),
        now()->startOfMonth()->addDays(16)->toDateString(),
        'forfait',
        null
    );

    expect($result)->toBeTrue();

    Mail::assertSent(ConfirmResa::class, 1);
    Mail::assertSent(NewResaForAdmin::class, 1);
});

it("reflète directement l'éligibilité via isForfait", function () {
    $contract = Contract::factory()->create();
    $tool     = makeTool(number: 5, contract: $contract);
    $user     = makeUser();

    $service = new SrvReservation();

    expect($service->isForfait($user, $tool))->toBeFalse();

    givePaidContract($user, $contract);

    expect($service->isForfait($user, $tool))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| needToPay
|--------------------------------------------------------------------------
*/
it("Ne demande a payer que dans l'état réservé et n'a pas payé", function() {
    $owner = makeUser();
    $tool  = makeTool();

    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $owner->id,
        'state'   => Reservation::STATE_RESERVED,
        'payment_state' => Reservation::PAYMENT_STATE_UNPAID,
    ]);

    actingAs($owner);

    $service = new SrvReservation();
    // RESERVED & Unpaid => TRUE
    expect($service->needToPay($reservation))->toBeTrue();
    
    // RESERVED & FORFAIT & no contract => TRUE
    $reservation->payment_state = Reservation::PAYMENT_STATE_FORFAIT;
    expect($service->needToPay($reservation))->toBeTrue();

    // RESERVED & TO PAY => FALSE
    $reservation->payment_state = Reservation::PAYMENT_STATE_TO_PAY;
    expect($service->needToPay($reservation))->toBeFalse();

    // RESERVED & PENDING HelloAsso=> FALSE
    $reservation->payment_state = Reservation::PAYMENT_STATE_HA_PENDING;
    expect($service->needToPay($reservation))->toBeFalse();

    // RESERVED & HelloAsso PAID => FALSE
    $reservation->payment_state = Reservation::PAYMENT_STATE_HA_PAYED;
    expect($service->needToPay($reservation))->toBeFalse();

    // RESERVED & PAID => FALSE
    $reservation->payment_state = Reservation::PAYMENT_STATE_PAYED;
    expect($service->needToPay($reservation))->toBeFalse();

    // PAYMENT in progress => FALSE
    $reservation->state = Reservation::STATE_PAYMENT;
    expect($service->needToPay($reservation))->toBeFalse();
    
    //CONFIRMED => FALSE
    $reservation->state = Reservation::STATE_CONFIRMED;
    expect($service->needToPay($reservation))->toBeFalse();

    //CANCELD => FALSE
    $reservation->state = Reservation::STATE_CANCELLED;
    expect($service->needToPay($reservation))->toBeFalse();

});

it("Demande a payer un forfait si le contrat n'est pas payé ", function() {
    $contract = Contract::factory()->create();
    $tool     = makeTool(number: 5, contract: $contract);
    $owner = makeUser();
    givePaidContract($owner,$contract,Reservation::PAYMENT_STATE_UNPAID);

    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $owner->id,
        'state'   => Reservation::STATE_RESERVED,
        'payment_state' => Reservation::PAYMENT_STATE_FORFAIT,
    ]);

    actingAs($owner);
    //dump(DB::select('SELECT * FROM contract_user'));

    $service = new SrvReservation();
    expect($service->needToPay($reservation))->toBeTrue();

});

it("Ne demande pas à payer un forfait si le contrat est payé ", function() {
    $contract = Contract::factory()->create();
    $tool     = makeTool(number: 5, contract: $contract);
    $owner = makeUser();

    givePaidContract($owner,$contract,Reservation::PAYMENT_STATE_PAYED);

    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $owner->id,
        'state'   => Reservation::STATE_RESERVED,
        'payment_state' => Reservation::PAYMENT_STATE_FORFAIT,
    ]);

    actingAs($owner);

    $service = new SrvReservation();
    expect($service->needToPay($reservation))->toBeFalse();
});


/*
|--------------------------------------------------------------------------
| Annulation
|--------------------------------------------------------------------------
*/

it("permet à un admin d'annuler n'importe quelle réservation", function () {
    $admin = makeUser('admin');
    $owner = makeUser();
    $tool  = makeTool();

    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $owner->id,
        'state'   => Reservation::STATE_RESERVED,
    ]);

    actingAs($admin);

    $service = new SrvReservation();
    $result  = $service->cancel($reservation);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    $this->assertDatabaseHas('journal_reservations', [
        'reference' => $reservation->reference,
        'state'     => Reservation::STATE_CANCELLED,
    ]);
});

it("permet au propriétaire d'annuler sa propre réservation réservée", function () {
    $owner = makeUser();
    $tool  = makeTool();

    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $owner->id,
        'state'   => Reservation::STATE_RESERVED,
    ]);

    actingAs($owner);

    $service = new SrvReservation();
    $result  = $service->cancel($reservation);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    $this->assertDatabaseHas('journal_reservations', [
        'reference' => $reservation->reference,
        'state'     => Reservation::STATE_CANCELLED,
    ]);
});

it("empêche le propriétaire d'annuler une réservation qui n'est pas à l'état réservé", function () {
    $owner = makeUser();
    $tool  = makeTool();

    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $owner->id,
        'state'   => Reservation::STATE_CANCELLED,
    ]);

    actingAs($owner);

    $service = new SrvReservation();
    $result  = $service->cancel($reservation);

    expect($result)->toBeFalse();
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
    expect($service->getMessage())->toBe("Vous n'êtes pas autorisé.");
});

it("empêche un autre utilisateur d'annuler la réservation de quelqu'un d'autre", function () {
    $owner = makeUser();
    $other = makeUser();
    $tool  = makeTool();

    $reservation = Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $owner->id,
        'state'   => Reservation::STATE_RESERVED,
    ]);

    actingAs($other);

    $service = new SrvReservation();
    $result  = $service->cancel($reservation);

    expect($result)->toBeFalse();
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
});