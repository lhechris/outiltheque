<x-filament-panels::page>
    <div class="space-y-6">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-[#1b716c] via-[#1e8b84] to-[#dff5f3] p-6 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl space-y-3 text-white">
                    <span class="inline-flex items-center rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/90">
                        Administration
                    </span>
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Bienvenue sur le tableau de bord</h1>
                    <p class="text-sm text-white/80 sm:text-base">
                        Suivi en temps réel des outils, réservations et activités du site.
                    </p>
                </div>

                <a
                    href="{{ route('tools.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"
                >
                    Retour au site
                </a>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Outils</div>
                <div class="mt-4 flex items-end justify-between">
                    <div class="text-3xl font-bold text-slate-900">{{ $this->toolsCount }}</div>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Catalogue</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Réservations</div>
                <div class="mt-4 flex items-end justify-between">
                    <div class="text-3xl font-bold text-slate-900">{{ $this->reservationsCount }}</div>
                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-700">Total</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Utilisateurs</div>
                <div class="mt-4 flex items-end justify-between">
                    <div class="text-3xl font-bold text-slate-900">{{ $this->usersCount }}</div>
                    <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-medium text-violet-700">Comptes</span>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.4fr_0.9fr]">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Accès rapide</h2>
                    <span class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Navigation</span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="/admin/tools" class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#1b716c] hover:bg-[#edf9f8]">
                        <div class="text-sm font-semibold text-slate-900">Outils</div>
                        <div class="mt-2 text-sm text-slate-600">Gérer le catalogue et les ressources.</div>
                    </a>

                    <a href="/admin/reservations" class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#1b716c] hover:bg-[#edf9f8]">
                        <div class="text-sm font-semibold text-slate-900">Réservations</div>
                        <div class="mt-2 text-sm text-slate-600">Suivre les demandes et les validations.</div>
                    </a>

                    <a href="/admin/users" class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#1b716c] hover:bg-[#edf9f8]">
                        <div class="text-sm font-semibold text-slate-900">Utilisateurs</div>
                        <div class="mt-2 text-sm text-slate-600">Consulter les comptes et profils.</div>
                    </a>

                    <a href="{{ route('tools.index') }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#1b716c] hover:bg-[#edf9f8]">
                        <div class="text-sm font-semibold text-slate-900">Site public</div>
                        <div class="mt-2 text-sm text-slate-600">Retourner au catalogue visible.</div>
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
