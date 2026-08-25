<?php

use App\Mail\ConfirmResa;
use App\Mail\NewResaForAdmin;
use App\Models\Parameter;
use App\Models\Reservation;
use App\Services\Helloasso\Payment;
use App\Services\Helloasso\Token;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Carbon\Carbon::setTestNow(Carbon\Carbon::parse('2026-08-10 10:00:00'));
});

afterEach(function () {
    Carbon\Carbon::setTestNow();
});

function fillReservationForm($component): mixed
{
    return $component
        ->set('date_start', '2026-08-13')
        ->set('name', 'Jean Dupont')
        ->set('email', 'jean.dupont@example.com')
        ->set('phone', '0601020304')
        ->set('reglement', true)
        ->set('paiement', 'unique');
}

it('sélectionne un outil, réserve puis paie à l\'unité', function () {
    $user = makeUser();
    $tool = makeTool(number: 2);

    actingAs($user);

    Livewire::test('tools.show', ['tool' => $tool])
        ->set('date_start', '2026-08-13')
        ->set('reglement', true)
        ->set('paiement', 'unique')
        ->call('reserver');

    $reservation = Reservation::query()->firstOrFail();

    expect($reservation->tool_id)->toBe($tool->id)
        ->and($reservation->user_id)->toBe($user->id)
        ->and($reservation->state)->toBe(Reservation::STATE_RESERVED)
        ->and($reservation->payment_state)->toBe(Reservation::PAYMENT_STATE_UNPAID);

    Livewire::test('paiements.select', ['ref' => $reservation->reference])
        ->assertSet('needToPay', true)
        ->call('handleCash');

    $reservation->refresh();

    expect($reservation->state)->toBe(Reservation::STATE_CONFIRMED)
        ->and($reservation->payment_state)->toBe(Reservation::PAYMENT_STATE_TO_PAY);

    Mail::assertSent(ConfirmResa::class);
    Mail::assertSent(NewResaForAdmin::class);
});

it('sélectionne un outil, réserve puis choisit HelloAsso', function () {
    $user = makeUser();
    $tool = makeTool(number: 2);

    actingAs($user);

    Livewire::test('tools.show', ['tool' => $tool])
        ->set('date_start', '2026-08-13')
        ->set('reglement', true)
        ->set('paiement', 'unique')
        ->call('reserver');

    $reservation = Reservation::query()->firstOrFail();

    /*  Mockery::mock('alias:' . Payment::class)
          ->shouldReceive('init')
          ->once()
          ->with(Mockery::on(fn ($r) => $r->is($reservation)), Mockery::type('int'));*/
    Mockery::mock('alias:'.Token::class)
        ->shouldReceive('refresh')
        ->once();

    Http::fake([
        '*' => Http::response([
            'id' => 'payment-123',
            'redirectUrl' => 'https://helloasso.com/checkout/payment-123',
        ], 200),
    ]);

    Parameter::factory()->create([
        'name' => config('helloasso.access_token_key'),
        'val' => 'test-access-token',
    ]);

    Livewire::test('paiements.select', ['ref' => $reservation->reference])
        ->assertSet('needToPay', true)
        ->call('handleHA');

    $reservation->refresh();

    expect($reservation->state)->toBe(Reservation::STATE_PAYMENT)
        ->and($reservation->payment_state)->toBe(Reservation::PAYMENT_STATE_HA_PENDING);

    // Le paiement est enregistré, pas de mail pour le moment
    // on attend une confirmation
    Mail::assertNothingSent();
});

it("Reçoit la confirmation HelloAsso, on verifie que c'est bien payé", function () {
    $user = makeUser();
    $tool = makeTool(number: 2);
    $reservation = Reservation::factory()->create([
        'state' => Reservation::STATE_PAYMENT,
        'payment_state' => Reservation::PAYMENT_STATE_HA_PENDING,
        'tool_id' => $tool->id,
        'user_id' => $user->id,
    ]);
    Parameter::factory()->create([
        'name' => config('helloasso.access_token_key'),
        'val' => 'test-access-token',
    ]);

    actingAs($user);

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
    Livewire::test('paiements.confirm', ['ref' => $reservation->reference]);

    $reservation->refresh();

    expect($reservation->state)->toBe(Reservation::STATE_CONFIRMED)
        ->and($reservation->payment_state)->toBe(Reservation::PAYMENT_STATE_HA_PAYED);

    Mail::assertSent(ConfirmResa::class);
    Mail::assertSent(NewResaForAdmin::class);
});
