<?php

use App\Mail\ConfirmResa;
use App\Mail\NewResaForAdmin;
use App\Models\Reservation;
use App\Models\Tool;
use App\Services\Helloasso\Payment;
use App\Services\SrvPayment;
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

    Mockery::mock('alias:' . Payment::class)
        ->shouldReceive('init')
        ->once()
        ->with(Mockery::on(fn ($r) => $r->is($reservation)), Mockery::type('int'));

    Livewire::test('paiements.select', ['ref' => $reservation->reference])
        ->assertSet('needToPay', true)
        ->call('handleHA');

    $reservation->refresh();

    expect($reservation->state)->toBe(Reservation::STATE_CONFIRMED)
        ->and($reservation->payment_state)->toBe(Reservation::PAYMENT_STATE_HA_PENDING);

    Mail::assertSent(ConfirmResa::class);
    Mail::assertSent(NewResaForAdmin::class);
});
