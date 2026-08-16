<div class="grid gap-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-lg bg-slate-50">
            <img class="max-h-16 max-w-16 object-contain" src="{{ asset('images/LB_logo.png') }}" alt="Logo" />
        </div>

        <div class="w-full">
            <div class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Nom</div>
            <div class="mt-1 text-2xl font-semibold text-slate-800">{{ $getRecord()?->name ?? '—' }}</div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Description</div>
            <div class="mt-2 text-sm text-slate-700">{{ $getRecord()?->description ?? '—' }}</div>
        </div>

        <div class="grid gap-4">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Icône</div>
                <div class="mt-2 text-sm text-slate-700">{{ $getRecord()?->icon ?? '—' }}</div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Image</div>
                <div class="mt-2 text-sm text-slate-700">{{ $getRecord()?->image ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Conseil d’utilisation</div>
            <div class="mt-2 text-sm text-slate-700">{!! $getRecord()?->advice ? str_replace('<ul>', '<ul class="list-disc list-inside pl-4">', $getRecord()->advice) : '—' !!}</div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <div class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Précaution</div>
            <div class="mt-2 text-sm text-slate-700">{!! $getRecord()?->caution ? str_replace('<ul>', '<ul class="list-disc list-inside pl-4">', $getRecord()->caution) : '—' !!}</div>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <div class="text-xs font-medium uppercase tracking-[0.14em] text-slate-500">Caractéristiques</div>

        @php($features = $getRecord()?->features ?? collect([]))

        @if($features->isNotEmpty())
            <div class="mt-3 grid gap-2 md:grid-cols-2">
                @foreach($features as $feature)
                    <div class="rounded border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                        <strong>{{ $feature->name }} :</strong> {{ $feature->val }}
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-3 text-sm text-slate-500">Aucune caractéristique associée.</div>
        @endif
    </div>
</div>