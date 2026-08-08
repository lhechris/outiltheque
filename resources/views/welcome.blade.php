<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
    <head>
        @include('partials.head')
    </head>
<body class="min-h-screen bg-white">

     <!--   <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ route('tools.index') }}"
                            class="inline-block px-5 py-1.5 text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Catalog
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            {{ __("Log in") }}
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                {{ __("Register") }}
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>-->

        <div>
            <nav class="fixed top-0 left-0 z-20 w-full border-b border-gray-200 bg-white py-2.5 px-6 sm:px-4">
                <div class="container mx-auto flex max-w-6xl flex-wrap items-center justify-between">
                    <a href="#" class="flex items-center w-auto">
                        <img class="max-h-12" src="/images/LB_logo.png">
                        <span class="self-center whitespace-nowrap text-xl font-semibold p-2">Outilthèque</span>
                    </a>
                    <div class="mt-2 sm:mt-0 sm:flex md:order-2">
                        <span class="text-sm px-4 py-1.5"></span>
                    </div>
                    <div class="items-center justify-between md:order-1 md:block md:w-auto" id="navbar-sticky">
                        <ul class="mt-4 flex flex-row rounded-lg border border-gray-100 bg-gray-50 p-4 md:mt-0 md:flex-row md:space-x-8 md:border-0 md:bg-blue md:text-sm md:font-medium"></ul>
                    </div>
                </div>
            </nav>

            <div class="w-6/12 p-10 mx-auto"></div>
                <div>
                <div class="flex flex-col overflow-x-auto overflow-y-hidden py-10 ml-2">
                    <p>Bienvenue sur l'outilthèque de Labobinette</p>
                    <p class="mt-5">Ce site est réservé aux adhérents de Labobinette</p>
                    <p class="mt-2">Le principe d'une outilthèque est de pouvoir emprunter un outil ou un objet pour une durée de temps défnie à la place de l’acheter.</p>
                    <p class="mt-2">L'outilthèque fonctionne comme une bibliothèque, mais pour le prêt d'outils. Il est possible d'emprunter du matériel de cuisine, des outils variés, du matériel de jardinage. </p>
                    <p class="mt-2">Ici, vous pourrez emprunter les objets pour une durée d'une semaine.</p><p class="mt-2">Plutôt que d'accumuler un ensemble d'outils encombrants chez soi, l'outilthèque permet une mise en commun.</p>
                    <p class="mt-5">Vous réservez, vous récupérez votre réservation le jeudi et vous le retournez le mercredi</p>
                    @auth
                    <a href="{{ route('tools.index') }}" class="mt-5 w-64 bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">Accéder aux produits</a>
                    @else
                    <a href="{{ route('login') }}" class="mt-5 w-64 bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">{{ __("Log in") }}</a>
                    <a href="{{ route('register') }}" class="mt-5 w-64 bg-blue-500 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">{{ __("Register") }}</a>
                    @endauth


                </div>
            </div>
            <footer class="py-6 bg-gray-200 text-gray-900">
                <div class="container px-6 mx-auto space-y-6 divide-y divide-gray-400 md:space-y-12 divide-opacity-50">
                    <div class="grid justify-center lg:justify-between">
                        <div class="flex flex-col self-center text-sm text-center md:block lg:col-start-1 md:space-x-6">
                            <span>CopyLeft ©2024 by labobinette dev team</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>