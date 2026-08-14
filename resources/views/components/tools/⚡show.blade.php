<?php

use Livewire\Component;
use App\Models\Tool;
use App\Models\Reservation;
use App\Services\SrvReservation;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;

new class extends Component
{
    public Tool $tool;
    public $debuts = [];

    #[Validate('required|date|after_or_equal:today')]
    public $date_start;

    #[Validate('required|date|after:date_start')]
    public $date_end;

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|email')]
    public $email;

    #[Validate('required|string|regex:/^[0-9+\s.-]{6,20}$/')]
    public $phone;

    #[Validate('nullable|string|max:1000')]
    public $comment;

    #[Validate('accepted')]
    public $reglement;

    #[Validate('required')]
    public string $paiement;

    public $user;

    public bool $hasForfait;

    public function mount()
    {
        $firstThursday = Carbon::now()->isDayOfWeek(Carbon::THURSDAY)
            ? Carbon::now()->startOfDay()
            : Carbon::now()->next(Carbon::THURSDAY);

        $this->debuts = collect(range(0, 8))
            ->map(fn ($i) => $firstThursday->copy()->addWeeks($i)->format('Y-m-d'))
            ->toArray();

        if (Auth::check()) {
            $this->user = Auth::user();
            $this->name = $this->user->firstname." ".$this->user->name;
            $this->phone = $this->user->phone;
            $this->email = $this->user->email;

            $this->hasForfait = app(SrvReservation::class)->isForfait($this->user,$this->tool);

            if ($this->hasForfait) {
                $this->paiement = "forfait";
            }

        } else {
            redirect(route("login"));
        }
    

    }

    public function updatedDateStart($value)
    {
        $this->date_end = $value
            ? Carbon::parse($value)->addDays(6)->format('Y-m-d')
            : null;
    }

    protected function messages()
    {
        return [
            'date_start.required'=> 'Merci de choisir une date de récupération.',
            'name.required'      => 'Le nom est obligatoire.',
            'name.min'           => 'Le nom doit contenir au moins 2 caractères.',
            'phone.required'     => 'Le téléphone est obligatoire.',
            'phone.regex'        => 'Le numéro de téléphone n\'est pas valide.',
            'email.required'     => 'L\'email est obligatoire.',
            'email.email'        => 'L\'email n\'est pas valide.',
            'reglement.accepted' => 'Vous devez accepter le règlement de l\'outilthèque.',
            'paiement.required'  => 'Merci de choisir votre type de paiement',
        ];
    }

    public function reserver(SrvReservation $srvResa)
    {
        if (!auth()->check()) { return;}

        $this->validate();

        //Si le téléphone à changé, on le met à jour
        if ($this->phone !== $this->user->phone) {
            $this->user->phone = $this->phone;
            $this->user->update();
        }

        if ($srvResa->create($this->user,$this->tool,$this->date_start,$this->date_end,$this->paiement,$this->comment)) {
            \Log::debug(auth()->user()->email." Réservation OK");
            session()->flash('success', $srvResa->getMessage());
            $this->reset(['date_start', 'date_end', 'name', 'email', 'phone', 'comment']);
            return (redirect(route('payments.select',[$srvResa->reservation->reference])));

        } else {
            \Log::info(auth()->user()->email."erreur résa ".$srvResa->getMessage());
            $this->addError('date_start',$srvResa->getMessage());
            return;
        }
    }

    public function annuler()
    {
        return redirect(route('tools.index'));
    }

};
?>
<div class="grid justify-center gap-5" >
    <div class="flex flex-col justify-start gap-4 max-w-sm sm:max-xl md:max-w-2xl lg:max-w-4xl">
        <div class="flex flex-col gap-4 font-['Arimo']">
            <div class="flex flex-row gap-4">
                <div class="size-32">
                    <img class="max-w-32" src="{{asset("images/LB_logo.png")}}" />
                </div>
                <div class="w-full"> 
                    <div class="text-[#1b716c] text-5xl font-bold font-['Comfortaa']" >{{ $tool->name }}</div>
                    <div class="border border-[#1b716c]"> </div>
                    <div class="max-w-xl">{{ $tool->description }}</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <img class="max-h-60" src="{{Storage::url($tool->icon)}}" alt="" />
                <div class="flex flex-col">
                    <div><img class="max-h-52" src="{{Storage::url($tool->image)}}" alt="" /></div>
                    <table >
                        @foreach($tool->features as $feature)
                        <tr class="odd:bg-olive-400 even:bg-olive-500">
                            <td><b>{{ $feature->name }}</b></td>
                            <td>{{ $feature->val }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="w-full bg-orange-100 mb-1 mx-2 font-['Comfortaa']">
                <div class="bg-teal-800 text-sm text-center py-2 font-bold">CONSEIL D'UTILISATION</div>
                <div class="py-2 "></ul>{!! str_replace("<ul>",'<ul class="list-disc list-inside">',$tool->advice) !!}</div>
            </div>
            </div>
            <div class="w-full bg-orange-100 mb-1 mx-2 font-['Comfortaa']">
                <div class="bg-orange-500 text-sm text-center py-2 font-bold">PRECAUTION</div>
                <div class="py-2" >{!! str_replace("<ul>",'<ul class="list-disc list-inside">',$tool->caution) !!}</div>
            </div>
        </div>
        @auth
        <div class="flex flex-row gap-4 py-4" >
            <div>
                <label for="selectdebut" class="mb-1 block text-sm font-medium text-gray-700">Date de récupération</label>
                <select id="selectdebut" 
                        wire:model.live="date_start"
                        class="px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed bg-blue-50"
                        >
                        <option value="">-- Choisir --</option>
                        @foreach($debuts as $d)
                            <option value="{{ $d }}">{{ \Carbon\Carbon::parse($d)->format('d/m') }}</option>
                        @endforeach
                        </select>
                @error('date_start') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="date_end" class="mb-1 block text-sm font-medium text-gray-700">Date de retour </label>
                <label  
                        class="px-4 py-2 block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        >{{ $date_start ? \Carbon\Carbon::parse($date_end)->format('d/m') : '-' }}</label>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 py-4" >
            <div>
                <label for="nom" class="mb-1 block text-sm font-medium text-gray-700">Nom</label>
                <input  type="text" 
                        id="nom" 
                        disabled
                        wire:model="name" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 pl-1 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Votre prénom et nom" />
                @error('name') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="telephone" class="mb-1 block text-sm font-medium text-gray-700">Téléphone</label>
                <input  type="text" 
                        id="telephone"                         
                        wire:model="phone" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 pl-1 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Numéro de téléphone" />
                @error('phone') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input  type="email" 
                        id="email" 
                        disabled
                        wire:model="email" 
                        class="block border rounded-md border-gray-300 shadow-sm focus:border-blue-400 pl-1 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                        placeholder="Votre Email" />
                @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            @if ($hasForfait)
            <div>
                <p>Vous avez un forfait.</p>
            </div>
            @else
            <div>
                <div class="flex items-center mb-4">
                <flux:radio.group wire:model="paiement" label="Votre type de paiement">
                    <flux:radio value="forfait" label="Je choisi un forfait"  />
                    <flux:radio value="unique" label="Je paye à l'unité" />
                </flux:radio.group>
                </div>
                @error('reglement') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
            @endif    
            <div>
                <div class="flex items-center mb-4">
                    <input id="default-checkbox" type="checkbox" wire:model="reglement" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="default-checkbox" class="ms-2 text-sm font-medium ">En cochant cette case, j'accepte le <a target="_blank" class="text-blue-600" href="/storage/uploads/Règlement intérieur de l’outilthèque.pdf" >réglement de l'outilthèque.</a></label>
                </div>
                @error('reglement') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>       
        </div>
        <div class="flex flex-row gap-4 pb-10" >        
            <button type="button" 
                class="rounded-lg border border-blue-500 bg-blue-500 px-5 py-2.5 text-center text-sm font-medium text-white shadow-sm transition-all hover:border-blue-700 hover:bg-blue-700 focus:ring focus:ring-blue-200 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                wire:click="reserver"
            >Réserver</button>
            <button type="button" 
                class="rounded-lg border border-blue-500 bg-blue-500 px-5 py-2.5 text-center text-sm font-medium text-white shadow-sm transition-all hover:border-blue-700 hover:bg-blue-700 focus:ring focus:ring-blue-200 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                wire:click="annuler"
            >Annuler</button>
        </div>
    </div>
    @endauth
</div>