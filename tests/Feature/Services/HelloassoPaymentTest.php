<?php

use App\Models\Parameter;
use App\Models\Reservation;
use App\Services\Helloasso\Payment;
use App\Services\Helloasso\Token;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;

uses(RefreshDatabase::class);

/**
 * Crée une réservation avec les relations nécessaires
 */
function makeHelloassoReservation(string $reference = 'REF-001'): Reservation
{
    $tool = makeTool();
    $user = makeUser();

    return Reservation::factory()->create([
        'reference' => $reference,
        'tool_id' => $tool->id,
        'user_id' => $user->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'state' => Reservation::STATE_RESERVED,
        'payment_state' => Reservation::PAYMENT_STATE_UNPAID,
    ]);
}

beforeEach(function () {
    // Mock la classe Token pour éviter les vraies appels HTTP
    $mockToken = Mockery::mock('alias:'.Token::class);
    $mockToken->shouldReceive('refresh')->andReturn(null);
});

// =====================================================================
// Tests de la méthode init()
// =====================================================================

it('initialise un paiement Helloasso avec succès', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-123',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-123',
        ], 200),
    ]);

    $reservation = makeHelloassoReservation('REF-001');
    $amount = 100;

    // Crée les paramètres nécessaires
    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $result = Payment::init($reservation, $amount);

    expect($result)->toBeTrue();
    expect($reservation->fresh()->payment_id)->toBe('payment-123');

    Http::assertSent(function ($request) {
        return $request->url() === env('HELLOASSO_ENCAISSEMENT_URL', '')
            && $request->method() === 'POST'
            && collect($request->data())->has(['totalAmount', 'payer']);
    });
});

it('retourne false si Helloasso répond avec une erreur', function () {
    Http::fake([
        '*' => Http::response(['error' => 'Invalid request'], 400),
    ]);

    $reservation = makeHelloassoReservation('REF-002');
    $amount = 100;

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $result = Payment::init($reservation, $amount);

    expect($result)->toBeFalse();
    expect($reservation->fresh()->payment_id)->toBeNull();
});

it('construit correctement le payload du paiement', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-456',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-456',
        ], 200),
    ]);

    $reservation = makeHelloassoReservation('REF-003');
    $amount = 50;

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    Payment::init($reservation, $amount);

    Http::assertSent(function ($request) use ($reservation, $amount) {
        $data = $request->data();

        return $data['totalAmount'] === $amount * 100
            && $data['initialAmount'] === $amount * 100
            && str_contains($data['itemName'], 'Location')
            && str_contains($data['itemName'], $reservation->reference)
            && $data['payer']['email'] === $reservation->email
            && $data['payer']['country'] === 'FRA'
            && $data['containsDonation'] === false;
    });
});

it('extrait correctement le prénom et le nom du client', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-789',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-789',
        ], 200),
    ]);

    $tool = makeTool();
    $user = makeUser();
    $reservation = Reservation::factory()->create([
        'name' => 'Jean Marie Dupont',
        'email' => 'jean@example.com',
        'state' => Reservation::STATE_RESERVED,
        'tool_id' => $tool->id,
        'user_id' => $user->id,
    ]);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    Payment::init($reservation, 75);

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $data['payer']['firstName'] === 'Jean'
            && $data['payer']['lastName'] === ' Marie Dupont';
    });
});

// =====================================================================
// Tests de la méthode check()
// =====================================================================

it('retourne true si le paiement est autorisé', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-100',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-100',
            'order' => [
                'payments' => [
                    ['state' => 'Authorized'],
                ],
            ],
        ], 200),
    ]);

    $reservation = makeHelloassoReservation('REF-004');
    $reservation->update(['payment_id' => 'payment-100']);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $result = Payment::check($reservation);

    expect($result)->toBeTrue();
});

it('retourne false si aucun paiement autorisé n\'existe', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-101',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-101',
            'order' => [
                'payments' => [
                    ['state' => 'Pending'],
                ],
            ],
        ], 200),
    ]);

    $reservation = makeHelloassoReservation('REF-005');
    $reservation->update(['payment_id' => 'payment-101']);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $result = Payment::check($reservation);

    expect($result)->toBeFalse();
});

it('retourne false si la réponse n\'a pas la structure attendue', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-102',
            // Missing redirectUrl and order
        ], 200),
    ]);

    $reservation = makeHelloassoReservation('REF-006');
    $reservation->update(['payment_id' => 'payment-102']);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $result = Payment::check($reservation);

    expect($result)->toBeFalse();
});

it('retourne false si Helloasso répond avec une erreur HTTP', function () {
    Http::fake([
        '*' => Http::response(['error' => 'Not found'], 404),
    ]);

    $reservation = makeHelloassoReservation('REF-007');
    $reservation->update(['payment_id' => 'payment-103']);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $result = Payment::check($reservation);

    expect($result)->toBeFalse();
});

it('retourne false si order est absent', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-104',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-104',
            // Missing order
        ], 200),
    ]);

    $reservation = makeHelloassoReservation('REF-008');
    $reservation->update(['payment_id' => 'payment-104']);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $result = Payment::check($reservation);

    expect($result)->toBeFalse();
});

it('retourne true si au moins un paiement est autorisé parmi plusieurs', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-105',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-105',
            'order' => [
                'payments' => [
                    ['state' => 'Pending'],
                    ['state' => 'Authorized'],
                    ['state' => 'Failed'],
                ],
            ],
        ], 200),
    ]);

    $reservation = makeHelloassoReservation('REF-009');
    $reservation->update(['payment_id' => 'payment-105']);

    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => 'test-access-token',
    ]);

    $result = Payment::check($reservation);

    expect($result)->toBeTrue();
});

it('utilise le bon token d\'accès pour les requêtes', function () {
    Http::fake([
        '*' => Http::response([
            'id' => 'payment-106',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-106',
            'order' => [
                'payments' => [
                    ['state' => 'Authorized'],
                ],
            ],
        ], 200),
    ]);

    $reservation = makeHelloassoReservation('REF-010');
    $reservation->update(['payment_id' => 'payment-106']);

    $token = 'custom-access-token-123';
    Parameter::factory()->create([
        'name' => 'ha_access_token',
        'val' => $token,
    ]);

    Payment::check($reservation);

    Http::assertSent(function ($request) use ($token) {
        return $request->hasHeader('Authorization', "Bearer {$token}");
    });
});
