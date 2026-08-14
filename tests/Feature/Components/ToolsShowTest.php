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
describe('Accès', function() {
    it("redirige vers la page de connexion si l'utilisateur n'est pas authentifié", function () {
        $tool = makeTool();

        Livewire::test('tools.show', ['tool' => $tool])
            ->assertRedirect(route('login'));
    });

    it("affiche les informations de l'outil pour un utilisateur authentifié", function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->assertSee($tool->name)
            ->assertSee($tool->description);
    });

    it('affiche les caractéristiques (features) du matériel', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        Feature::factory()->create([
            'tool_id' => $tool->id,
            'name'    => 'Puissance',
            'val'   => '750W',
        ]);

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->assertSee('Puissance');
    });
});
/*
|--------------------------------------------------------------------------
| mount()
|--------------------------------------------------------------------------
*/
describe('Mount', function () {
    it("pré-remplit nom, téléphone et email avec les données de l'utilisateur connecté", function () {
        $user = User::factory()->create([
            'firstname' => 'Jean',
            'name'      => 'Dupont',
            'phone'     => '0601020304',
            'email'     => 'jean.dupont@example.com',
        ]);
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->assertSet('name', 'Jean Dupont')
            ->assertSet('phone', '0601020304')
            ->assertSet('email', 'jean.dupont@example.com');
    });

    it('génère 9 jeudis à partir du prochain jeudi', function () {
        // 10/06/2025 est un mardi -> le prochain jeudi est le 12/06/2025
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        $debuts = Livewire::test('tools.show', ['tool' => $tool])->get('debuts');

        expect($debuts)->toHaveCount(9)
            ->and($debuts[0])->toBe('2025-06-12');

        foreach ($debuts as $debut) {
            expect(Carbon::parse($debut)->isDayOfWeek(Carbon::THURSDAY))->toBeTrue();
        }
    });

    it('inclut le jour même comme première date si on est déjà un jeudi', function () {
        Carbon::setTestNow(Carbon::parse('2025-06-12 09:00:00')); // un jeudi

        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        $debuts = Livewire::test('tools.show', ['tool' => $tool])->get('debuts');

        expect($debuts[0])->toBe('2025-06-12');
    });

    it("N'affiche pas la selection du type de paiement si hasForfait est vrai", function () {
        $user     = User::factory()->create();
        $contract = Contract::factory()->create();
        $tool     = makeTool(1,$contract);

        $this->mock(SrvReservation::class, function ($mock) {
            $mock->shouldReceive('isForfait')->once()->andReturn(true);
        });

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->assertSet('hasForfait', true);
    });

    it("Affiche la selection du type de paiement si hasForfait est faux", function () {
        $user     = User::factory()->create();
        $contract = Contract::factory()->create();
        $tool     = makeTool(1,$contract);

        $this->mock(SrvReservation::class, function ($mock) {
            $mock->shouldReceive('isForfait')->once()->andReturn(false);
        });

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->assertSet('hasForfait', false);
    });
});
/*
|--------------------------------------------------------------------------
| updatedDateStart()
|--------------------------------------------------------------------------
*/
describe('Date',function () {
    it('positionne automatiquement date_end à J+6 quand date_start change', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('date_start', '2025-06-19')
            ->assertSet('date_end', '2025-06-25');
    });

    it('remet date_end à null si date_start est vidé', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('date_start', '2025-06-19')
            ->set('date_start', '')
            ->assertSet('date_end', null);
    });
});
/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/
describe("Validation", function () {
    it('rejette une réservation sans date de récupération', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('date_start', '')
            ->call('reserver')
            ->assertHasErrors(['date_start' => 'required']);
    });

    it('rejette une date de récupération dans le passé', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('date_start', '2025-01-01')
            ->call('reserver')
            ->assertHasErrors(['date_start' => 'after_or_equal']);
    });

    it('rejette une date de retour antérieure ou égale à la date de récupération', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('date_start', '2025-06-19')
            ->set('date_end', '2025-06-10')
            ->call('reserver')
            ->assertHasErrors(['date_end' => 'after']);
    });

    it('rejette un nom vide', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('name', '')
            ->call('reserver')
            ->assertHasErrors(['name' => 'required']);
    });

    it('rejette un numéro de téléphone au mauvais format', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('phone', 'pas-un-numero')
            ->call('reserver')
            ->assertHasErrors(['phone' => 'regex']);
    });

    it('accepte les formats de téléphone valides', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('phone', '06 01 02 03 04')
            ->call('reserver')
            ->assertHasNoErrors(['phone']);
    });

    it('rejette un email invalide', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('email', 'pas-un-email')
            ->call('reserver')
            ->assertHasErrors(['email' => 'email']);
    });

    it('rejette un commentaire trop long', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('comment', str_repeat('a', 1001))
            ->call('reserver')
            ->assertHasErrors(['comment' => 'max']);
    });

    it('accepte un commentaire vide (nullable)', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('comment', null)
            ->call('reserver')
            ->assertHasNoErrors(['comment']);
    });

    it("rejette la réservation si le règlement n'est pas accepté", function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('reglement', false)
            ->call('reserver')
            ->assertHasErrors(['reglement' => 'accepted']);
    });

    it('rejette la réservation sans mode de paiement choisi', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('paiement', '')
            ->call('reserver')
            ->assertHasErrors(['paiement' => 'required']);
    });
});
/*
|--------------------------------------------------------------------------
| reserver()
|--------------------------------------------------------------------------
*/
function fillValidReservationForm($component)
{
    return $component
        ->set('date_start', '2025-06-19')
        ->set('name', 'Jean Dupont')
        ->set('email', 'jean.dupont@example.com')
        ->set('phone', '0601020304')
        ->set('reglement', true)
        ->set('paiement', 'unique');
}
describe("Réservation", function() {

    it('crée la réservation et redirige vers la page de paiement en cas de succès', function () {
        $user       = User::factory()->create();
        $tool       = makeTool();
        $reservation = Reservation::factory()->make();

        $this->mock(SrvReservation::class, function ($mock) use ($reservation) {
            $mock->shouldReceive('isForfait')->andReturn(false);
            $mock->shouldReceive('create')->once()->andReturn(true);
            $mock->shouldReceive('getMessage')->andReturn('Réservation effectuée avec succès.');
            $mock->reservation = $reservation;
        });

        actingAs($user);

        $component = fillValidReservationForm(
            Livewire::test('tools.show', ['tool' => $tool])
        );

        $component
            ->call('reserver')
            ->assertRedirect(route('payments.select', [$reservation->reference]))
            ->assertSet('date_start', null)
            ->assertSet('name', null)
            ->assertSet('email', null)
            ->assertSet('phone', null)
            ->assertSet('comment', null);

        expect(session('success'))->toBe('Réservation effectuée avec succès.');
    });

    it('ajoute une erreur sur date_start et ne redirige pas si la création échoue', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        $this->mock(SrvReservation::class, function ($mock) {
            $mock->shouldReceive('isForfait')->andReturn(false);
            $mock->shouldReceive('create')->once()->andReturn(false);
            $mock->shouldReceive('getMessage')
                ->andReturn("Ce matériel n'est plus disponible sur cette période.");
        });

        actingAs($user);

        $component = fillValidReservationForm(
            Livewire::test('tools.show', ['tool' => $tool])
        );

        $component
            ->call('reserver')
            ->assertHasErrors(['date_start'])
            ->assertNoRedirect();
    });

    it('ne crée pas de réservation si la validation échoue', function () {
        $user = User::factory()->create();
        $tool = makeTool();

        $this->mock(SrvReservation::class, function ($mock) {
            $mock->shouldReceive('isForfait')->andReturn(false);
            $mock->shouldNotReceive('create');
        });

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->set('date_start', '')
            ->call('reserver')
            ->assertHasErrors(['date_start']);
    });
});
/*
|--------------------------------------------------------------------------
| annuler()
|--------------------------------------------------------------------------
*/
describe("Annulation", function() {
    it("redirige vers la liste des outils lors de l'annulation", function () {
        $user = User::factory()->create();
        $tool = makeTool();

        actingAs($user);

        Livewire::test('tools.show', ['tool' => $tool])
            ->call('annuler')
            ->assertRedirect(route('tools.index'));
    });
});