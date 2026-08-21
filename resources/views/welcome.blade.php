<?php
    function getColorClass($color): string
    {
        return match ($color) {
            'purple' => 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-200',
            'green'  => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200',
            'emerald' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
            'red'    => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
            'amber'  => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
            'orange' => 'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-200',
            'violet' => 'bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-200',
            'blue'   => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',
            'yellow' => 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200',
            'lime' => 'bg-lime-50 text-lime-700 ring-1 ring-inset ring-lime-200',
            'teal' => 'bg-teal-50 text-teal-700 ring-1 ring-inset ring-teal-200',
            'cyan' => 'bg-cyan-50 text-cyan-700 ring-1 ring-inset ring-cyan-200',
            'indigo' => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200',            
            'pink' => 'bg-pink-50 text-pink-700 ring-1 ring-inset ring-pink-200',            
            'rose' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200',            
            default  => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-200',
        };
    } 

?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
    <head>
        @include('partials.head')
    </head>
<body class="min-h-screen bg-[#f4f0e8] font-sans text-[#25352d] antialiased">
    <div class="relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 -z-10 h-[34rem] bg-[radial-gradient(circle_at_85%_5%,rgba(216,109,67,0.2),transparent_32%),radial-gradient(circle_at_8%_18%,rgba(100,130,91,0.24),transparent_32%)]"></div>

        <nav class="relative mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8" aria-label="Navigation principale">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Accueil Outilthèque">
                <img class="h-12 w-12 object-contain" src="/images/LB_logo.png" alt="Logo Labobinette">
                <span class="font-[Comfortaa] text-lg font-bold tracking-tight">Outilthèque</span>
            </a>
            <div class="flex items-center gap-3 text-sm font-semibold">
                <a href="#tarifs" class="hidden text-[#52665b] transition hover:text-[#d86d43] sm:inline">Tarifs</a>
                @auth
                    <a href="{{ route('tools.index') }}" class="rounded-full bg-[#25352d] px-4 py-2.5 text-white transition hover:bg-[#d86d43]">Voir les outils</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full border border-[#b9c3b6] px-4 py-2.5 text-[#25352d] transition hover:border-[#25352d]">Se connecter</a>
                @endauth
            </div>
        </nav>

        <main>
            <section class="mx-auto grid max-w-7xl items-center gap-12 px-6 pb-20 pt-14 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:pb-28 lg:pt-24">
                <div class="max-w-2xl">
                    <p class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/75 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-[#d86d43] shadow-sm ring-1 ring-[#e3d8c7]">
                        <span class="h-2 w-2 rounded-full bg-[#d86d43]"></span>
                        Labobinette · partage local
                    </p>
                    <h1 class="font-[Comfortaa] text-4xl font-bold leading-[1.12] tracking-tight text-[#25352d] sm:text-5xl lg:text-6xl">
                        Emprunter plus.<br><span class="text-[#d86d43]">Acheter moins.</span>
                    </h1>
                    <p class="mt-7 max-w-xl text-lg leading-8 text-[#52665b]">
                        Bienvenue dans l'outilthèque de Labobinette : une bibliothèque d'objets et d'outils pour bricoler, cuisiner et jardiner sans encombrer vos placards.
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <a href="{{ route('tools.index') }}" class="inline-flex items-center justify-center gap-3 rounded-full bg-[#d86d43] px-6 py-3.5 font-bold text-white shadow-lg shadow-[#d86d43]/20 transition hover:-translate-y-0.5 hover:bg-[#bf5933]">Explorer le catalogue <span aria-hidden="true">→</span></a>
                        @else
                           <!-- <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-3 rounded-full bg-[#d86d43] px-6 py-3.5 font-bold text-white shadow-lg shadow-[#d86d43]/20 transition hover:-translate-y-0.5 hover:bg-[#bf5933]">Créer mon compte <span aria-hidden="true">→</span></a>-->
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-[#b9c3b6] bg-white/50 px-6 py-3.5 font-bold text-[#25352d] transition hover:bg-white">Je me connecte</a>
                        @endauth
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-lg">
                    <div class="absolute -inset-5 -z-10 rotate-3 rounded-[2.5rem] bg-[#d9e1d3]"></div>
                    <div class="rounded-[2.5rem] bg-[#25352d] p-7 text-[#f4f0e8] shadow-2xl shadow-[#25352d]/20 sm:p-10">
                        <div class="flex items-start justify-between border-b border-white/15 pb-8">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#b9c9ad]">Le principe</p>
                                <h2 class="mt-3 font-[Comfortaa] text-2xl font-bold">Une ressource,<br>des dizaines de projets.</h2>
                            </div>
                            <span class="text-4xl" aria-hidden="true">✦</span>
                        </div>
                        <div class="mt-8 space-y-7">
                            <div class="flex gap-4"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#d86d43] font-bold">01</span><p class="pt-1 leading-6 text-[#dce6d8]">Choisissez l'outil dont vous avez besoin.</p></div>
                            <div class="flex gap-4"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#d86d43] font-bold">02</span><p class="pt-1 leading-6 text-[#dce6d8]">Réservez-le pour une semaine.</p></div>
                            <div class="flex gap-4"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#d86d43] font-bold">03</span><p class="pt-1 leading-6 text-[#dce6d8]">Récupérez-le jeudi, retournez-le mercredi.</p></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-y border-[#ded5c7] bg-[#fffdf8]" id="tarifs">
                <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8 lg:py-20">
                    <div class="max-w-3xl">
                        <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#d86d43]">Les tarifs, en toute transparence</p>
                        <h2 class="mt-3 font-[Comfortaa] text-3xl font-bold tracking-tight sm:text-4xl">Deux façons d'emprunter.</h2>
                        <p class="mt-4 leading-7 text-[#52665b]">Vous choisissez entre un paiement à l'unité ou un forfait. Le montant du forfait dépend du type d'outil emprunté.</p>
                    </div>

                    <div class="mt-10 grid gap-5 lg:grid-cols-2">
                        <article class="rounded-2xl bg-[#25352d] p-7 text-[#f4f0e8] shadow-xl shadow-[#25352d]/10">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#b9c9ad]">Choix 01</p>
                                    <h3 class="mt-3 font-[Comfortaa] text-2xl font-bold">Paiement à l'unité</h3>
                                </div>
                                <span class="text-3xl text-[#d86d43]" aria-hidden="true">◒</span>
                            </div>
                            <p class="mt-5 leading-7 text-[#dce6d8]">Vous payez uniquement lorsque vous réservez un outil. Le prix est indiqué pour chaque type d'outil ci-dessous.</p>
                            <p class="mt-6 text-sm font-semibold text-[#b9c9ad]">Idéal pour un besoin ponctuel.</p>
                        </article>

                        <article class="rounded-2xl border border-[#d8cbb9] bg-[#f4f0e8] p-7">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#d86d43]">Choix 02</p>
                                    <h3 class="mt-3 font-[Comfortaa] text-2xl font-bold">Forfait par type d'outil</h3>
                                </div>
                                <span class="text-3xl text-[#d86d43]" aria-hidden="true">✦</span>
                            </div>
                            <p class="mt-5 leading-7 text-[#52665b]">Vous choisissez un forfait selon le type d'outil. Il vous donne accès aux outils concernés pendant sa période de validité.</p>
                            <p class="mt-6 text-sm font-semibold text-[#8a5a37]">Les forfaits sont valables jusqu'au 31 décembre.</p>
                        </article>
                    </div>

                    <div class="mt-8 overflow-hidden rounded-2xl border border-[#ded5c7] bg-white">
                        <div class="grid grid-cols-[1.3fr_0.8fr_0.8fr] gap-4 border-b border-[#ded5c7] bg-[#ebe4d8] px-5 py-4 text-xs font-bold uppercase tracking-[0.12em] text-[#52665b] sm:px-7">
                            <span>Type d'outil</span>
                            <span>À l'unité</span>
                            <span>Forfait</span>
                        </div>
                        @foreach($contrats as $contrat)
                            <div class="grid grid-cols-[1.3fr_0.8fr_0.8fr] items-center gap-4 border-b border-[#eee8de] px-5 py-5 last:border-0 sm:px-7 {{getColorClass($contrat->color) }}">
                                <div>
                                    <h3 class="font-[Comfortaa] font-bold">{{ $contrat->name }}</h3>
                                </div>
                                <span class="font-bold">
                                    {{ number_format($contrat->unit, 2, ',', ' ') }} €       
                                </span>
                                <span class="font-bold">
                                    {{ number_format($contrat->flat_rate, 2, ',', ' ') }} €
                                     @if($contrat->restriction)
                                        <p class="mt-1 text-xs text-[#718174]">{{ $contrat->restriction }}</p>
                                    @endif

                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <footer class="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-8 text-sm text-[#718174] sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <span>CopyLeft ©2024 Labobinette dev team</span>
        </footer>
    </div>
</body>
</html>