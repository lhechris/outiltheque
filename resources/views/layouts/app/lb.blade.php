<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white">

        {{-- Sidebar mobile (drawer) --}}
        <flux:sidebar stashable sticky class="lg:hidden bg-white border-r border-zinc-200">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('tools.index') }}" class="flex items-center gap-2 mb-4">
                <img src="{{ asset('images/LB_logo.png') }}" alt="Outilthèque" class="h-8 w-8 rounded">
                <span class="font-semibold text-lg text-zinc-900">Outilthèque</span>
            </a>

            @if(auth()->check())
                <flux:navlist>
                    <flux:navlist.item icon="book-open" href="{{ route('mesreservations.index') }}">Mes réservations</flux:navlist.item>
                    <flux:navlist.item icon="document" href="{{ route('mescontracts.index') }}">Mes contrats</flux:navlist.item>

                    @if(auth()->user()->isAdmin())
                        <flux:navlist.group heading="Administration" class="mt-4">
                            <flux:navlist.item icon="book-open" href="{{ route('reservations.index') }}">Reservations</flux:navlist.item>
                            <flux:navlist.item icon="document" href="{{ route('contracts.index') }}">Contrats</flux:navlist.item>
                            <flux:navlist.item icon="adjustments-horizontal" href="/admin">Administration</flux:navlist.item>
                        </flux:navlist.group>
                    @endif
                </flux:navlist>
            @endif
        </flux:sidebar>

        {{-- Barre de menu principale --}}
        <flux:header container class="bg-white border-b border-zinc-200">
            {{-- Bouton hamburger (mobile uniquement) --}}
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            {{-- Logo + Nom de l'appli --}}
            <a href="{{ route('tools.index') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('images/LB_logo.png') }}" alt="Outilthèque" class="h-8 w-8 rounded">
                <span class="font-semibold text-lg text-zinc-900">Outilthèque</span>
            </a>

            {{-- Groupe gauche : liens utilisateur --}}
            @if(auth()->check())
                <flux:navbar class="-mb-px max-lg:hidden">
                    <flux:navbar.item icon="book-open" href="{{ route('mesreservations.index') }}" current>Mes réservations</flux:navbar.item>
                    <flux:navbar.item icon="document" href="{{ route('mescontracts.index') }}" current>Mes contrats</flux:navbar.item>
                </flux:navbar>
            @endif

            <flux:spacer />

            {{-- Groupe droite : liens admin --}}
            @if(auth()->check() && auth()->user()->isAdmin())
                <flux:navbar class="-mb-px max-lg:hidden">
                    <flux:navbar.item icon="book-open" href="{{ route('reservations.index') }}" current>Reservations</flux:navbar.item>
                    <flux:navbar.item icon="document" href="{{ route('contracts.index') }}" current>Contrats</flux:navbar.item>
                    <flux:navbar.item icon="adjustments-horizontal" href="/admin" current>Administration</flux:navbar.item>
                </flux:navbar>
            @endif

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