<?php

use App\Mail\ConfirmResa;
use App\Mail\NewResaForAdmin;
use App\Models\Contract;
use App\Models\Parameter;
use App\Models\Reservation;
use App\Models\Tool;
use App\Services\Helloasso\Token;
use App\Services\SrvPayment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Mockery;

uses(RefreshDatabase::class);

/**
 * Construit une réservation "prête à payer" : STATE_RESERVED,
 * rattachée à un tool -> contract, avec un payment_state donné.
 */
function makeReservation(string $paymentState, array $contractAttrs = []): Reservation
{
    $contract = Contract::factory()->create(array_merge([
        'unit' => 10,
        'flat_rate' => 50,
    ], $contractAttrs));

    $tool = makeTool(1, $contract);
    $user = makeUser();

    return Reservation::factory()->create([
        'tool_id' => $tool->id,
        'user_id' => $user->id,
        'state' => Reservation::STATE_RESERVED,
        'payment_state' => $paymentState,
    ]);
}

beforeEach(function () {
    Mail::fake();
    config(['mail.responsable_resa' => 'admin@club.test']);
    putenv('MAIL_RESPONSABLE_RESA=admin@club.test');

    // Mardi 10/06/2025, pour avoir un point de départ stable.
    Carbon::setTestNow(Carbon::parse('2025-06-10 10:00:00'));
});

// ---------------------------------------------------------------------
// Etats invalides / réservation non payable
// ---------------------------------------------------------------------

it("refuse le paiement cash si la réservation n'est pas à l'état réservé", function () {
    $reservation = makeReservation(Reservation::PAYMENT_STATE_UNPAID);
    $reservation->state = Reservation::STATE_CONFIRMED;
    $reservation->save();

    $srv = new SrvPayment;
    $result = $srv->pay_by_cash($reservation);

    expect($result)->toBeFalse();
    expect($srv->getMessage())->toBe('Impossible de payer cette réservation');
    Mail::assertNothingSent();
});

it("refuse le paiement HelloAsso si la réservation n'est pas à l'état réservé", function () {
    $reservation = makeReservation(Reservation::PAYMENT_STATE_UNPAID);
    $reservation->state = Reservation::STATE_CANCELLED;
    $reservation->save();

    $srv = new SrvPayment;
    $result = $srv->pay_by_ha($reservation);

    expect($result)->toBeFalse();
    Mail::assertNothingSent();
});

it('refuse le paiement si le payment_state de la réservation est incohérent', function () {
    // Ex: déjà marqué "Payé Helloasso" alors qu'on tente de la payer à nouveau
    $reservation = makeReservation(Reservation::PAYMENT_STATE_HA_PAYED);

    $srv = new SrvPayment;
    $result = $srv->pay_by_cash($reservation);

    expect($result)->toBeFalse();
    expect($srv->getMessage())->toBe("Problème d'état avec la réservation, veuillez consulter les administrateurs.");
    expect($reservation->fresh()->state)->toBe(Reservation::STATE_RESERVED);
    Mail::assertNothingSent();
});

// ---------------------------------------------------------------------
// Paiement à l'unité (payment_state = UNPAID)
// ---------------------------------------------------------------------

it("paie une réservation à l'unité en cash", function () {
    $reservation = makeReservation(Reservation::PAYMENT_STATE_UNPAID, ['unit' => 15]);

    $srv = new SrvPayment;
    $result = $srv->pay_by_cash($reservation);

    expect($result)->toBeTrue();
    $reservation->refresh();
    expect($reservation->payment_state)->toBe(Reservation::PAYMENT_STATE_TO_PAY);
    expect($reservation->state)->toBe(Reservation::STATE_CONFIRMED);

    Mail::assertSent(ConfirmResa::class, fn ($mail) => $mail->hasTo($reservation->email));
    Mail::assertSent(NewResaForAdmin::class);
});

it("paie une réservation à l'unité via HelloAsso et initialise le paiement", function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-ha-123',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-ha-123',
        ], 200),
    ]);

    $mockToken = Mockery::mock('alias:'.Token::class);
    $mockToken->shouldReceive('refresh')->andReturn(null);

    $reservation = makeReservation(Reservation::PAYMENT_STATE_UNPAID, ['unit' => 20]);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $srv = new SrvPayment;
    $result = $srv->pay_by_ha($reservation);

    expect($result)->toBeTrue();
    $reservation->refresh();

    expect($reservation->payment_state)->toBe(Reservation::PAYMENT_STATE_HA_PENDING);
    expect($reservation->state)->toBe(Reservation::STATE_PAYMENT);
    expect($reservation->payment_id)->toBe('payment-ha-123');
});

// ---------------------------------------------------------------------
// Paiement au forfait (payment_state = FORFAIT) - pivot contrat inexistant
// ---------------------------------------------------------------------

it('crée le pivot contrat (attach) lors d\'un paiement forfait cash sans contrat existant', function () {
    $reservation = makeReservation(Reservation::PAYMENT_STATE_FORFAIT, ['flat_rate' => 50]);

    $srv = new SrvPayment;
    $result = $srv->pay_by_cash($reservation);

    expect($result)->toBeTrue();

    $pivot = $reservation->user->contracts()
        ->where('contracts.id', $reservation->tool->contract_id)
        ->first()
        ->pivot;

    expect($pivot->payment_state)->toBe(Reservation::PAYMENT_STATE_TO_PAY);
    expect($reservation->fresh()->state)->toBe(Reservation::STATE_CONFIRMED);
});

it('crée le pivot contrat (attach) lors d\'un paiement forfait HelloAsso sans contrat existant', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-forfait-123',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-forfait-123',
        ], 200),
    ]);

    $mockToken = Mockery::mock('alias:'.Token::class);
    $mockToken->shouldReceive('refresh')->andReturn(null);

    $reservation = makeReservation(Reservation::PAYMENT_STATE_FORFAIT, ['flat_rate' => 50]);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $srv = new SrvPayment;
    $result = $srv->pay_by_ha($reservation);

    expect($result)->toBeTrue();

    $pivot = $reservation->user->contracts()
        ->where('contracts.id', $reservation->tool->contract_id)
        ->first()
        ->pivot;

    expect($pivot->payment_state)->toBe(Reservation::PAYMENT_STATE_HA_PENDING);
});

// ---------------------------------------------------------------------
// Paiement au forfait - pivot contrat déjà existant et non payé
// ---------------------------------------------------------------------

it('met à jour le pivot contrat existant (UNPAID) lors d\'un paiement forfait cash', function () {
    $reservation = makeReservation(Reservation::PAYMENT_STATE_FORFAIT, ['flat_rate' => 50]);

    $reservation->user->contracts()->attach($reservation->tool->contract_id, [
        'payment_state' => Reservation::PAYMENT_STATE_UNPAID,
        'begin' => now(),
        'expire' => '2026-08-31',
        'begin' => now(),
    ]);

    $srv = new SrvPayment;
    $result = $srv->pay_by_cash($reservation);

    expect($result)->toBeTrue();

    $pivot = $reservation->user->contracts()
        ->where('contracts.id', $reservation->tool->contract_id)
        ->first()
        ->pivot;

    expect($pivot->payment_state)->toBe(Reservation::PAYMENT_STATE_TO_PAY);
});

// ---------------------------------------------------------------------
// Paiement au forfait - pivot contrat déjà dans un état incohérent
// ---------------------------------------------------------------------

it('refuse le paiement forfait cash si le pivot contrat est déjà dans un état non UNPAID', function () {
    $reservation = makeReservation(Reservation::PAYMENT_STATE_FORFAIT, ['flat_rate' => 50]);

    $reservation->user->contracts()->attach($reservation->tool->contract_id, [
        'payment_state' => Reservation::PAYMENT_STATE_HA_PAYED,
        'expire' => '2027-08-31',
        'begin' => now(),
    ]);

    $srv = new SrvPayment;
    $result = $srv->pay_by_cash($reservation);

    expect($result)->toBeFalse();
    expect($srv->getMessage())->toBe("Problème d'état avec la réservation, veuillez consulter les administrateurs.");

    // Le pivot n'a pas dû être modifié
    $pivot = $reservation->user->contracts()
        ->where('contracts.id', $reservation->tool->contract_id)
        ->first()
        ->pivot;
    expect($pivot->payment_state)->toBe(Reservation::PAYMENT_STATE_HA_PAYED);

    // La réservation n'a pas dû être confirmée
    expect($reservation->fresh()->state)->toBe(Reservation::STATE_RESERVED);
    Mail::assertNothingSent();
});

it('refuse le paiement forfait HelloAsso si le pivot contrat est déjà dans un état non UNPAID', function () {
    $reservation = makeReservation(Reservation::PAYMENT_STATE_FORFAIT, ['flat_rate' => 50]);

    $reservation->user->contracts()->attach($reservation->tool->contract_id, [
        'payment_state' => Reservation::PAYMENT_STATE_PAYED,
        'expire' => '2027-08-31',
        'begin' => now(),
    ]);

    $srv = new SrvPayment;
    $result = $srv->pay_by_ha($reservation);

    expect($result)->toBeFalse();
    Mail::assertNothingSent();
});

// ---------------------------------------------------------------------
// Emails
// ---------------------------------------------------------------------

it('envoie les emails avec le bon montant', function () {
    $reservation = makeReservation(Reservation::PAYMENT_STATE_UNPAID, ['unit' => 33]);

    $srv = new SrvPayment;
    $srv->pay_by_cash($reservation);

    Mail::assertSent(ConfirmResa::class, function ($mail) use ($reservation) {
        return $mail->hasTo($reservation->email)
            && invade($mail)->amount === 33;
    });
    Mail::assertSent(NewResaForAdmin::class, 1);
});
