<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white">

        {{-- Barre de menu principale --}}
        <flux:header container class="bg-white border-b border-zinc-200">

            {{-- Logo + Nom de l'appli --}}
            <a href="{{ route('tools.index') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('images/LB_logo.png') }}" alt="Outilthèque" class="h-8 w-8 rounded">
                <span class="font-semibold text-lg text-zinc-900">
                    Outilthèque
                </span>
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">            
            @if(auth())
               <flux:navbar.item icon="book-open" href="{{route('mesreservations.index')}}" current>Mes réservations</flux:navbar.item>
            @if( auth()->user()->isAdmin())
                <flux:navbar.item icon="book-open" href="{{route('reservations.index')}}" current>Reservations</flux:navbar.item>
                <flux:navbar.item icon="adjustments-horizontal" href="/admin" current>Administration</flux:navbar.item>
            @endif
            @endif
            </flux:navbar>
            <flux:spacer />


            @auth
                {{-- Menu utilisateur --}}
                <flux:dropdown position="bottom" align="end">
                    <flux:profile
                        avatar:initials="{{ auth()->user()->initials() ?? Str::of(auth()->user()->firstname)->explode(' ')->map(fn($p) => Str::substr($p, 0, 1))->join('') }}"
                        name="{{ auth()->user()->firstname }}"
                    />

                    <flux:menu>
                        <flux:menu.item icon="user" href="{{ route('profile.edit') }}">
                            Profil
                        </flux:menu.item>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" variant="danger">
                                Déconnexion
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @endauth

        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist
        @fluxScripts
    </body>
</html>