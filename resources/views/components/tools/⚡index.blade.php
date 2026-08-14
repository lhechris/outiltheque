<?php

use App\Models\Category;
use App\Models\Tool;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'categorie')]
    public ?int $categoryId = null;

    #[Url]
    public string $search = '';

    public ?int $showToolId = null;

    public function selectCategory(?int $id = null): void
    {
        $this->categoryId = $id ? (int) $id : null;
    }

    public function showTool(int $id): void
    {
        $this->showToolId = $id;
        Tool::whereKey($id)->increment('views');
    }

    public function closeTool(): void
    {
        $this->showToolId = null;
    }

    public function with(): array
    {
        return [
            'categories' => Category::query()
                ->withCount(['tools' => fn ($q) => $q->where('active', true)])
                ->orderBy('name')
                ->get(),
            'tools' => Tool::query()
                ->with(['category', 'contract'])
                ->where('active', true)
                ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->get(),
        ];
    }
}
?>

<div class="min-h-screen bg-gray-50">

    {{-- Header + barre de recherche --}}
    <div class="max-w-7xl mx-auto p-4 sm:p-6">
        {{-- Menu catégories horizontal, toujours visible --}}
        <nav class="md:flex grid grid-cols-3 gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-thin">
            <button
                wire:click="selectCategory"
                class="shrink-0 flex items-center gap-0.5 px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition
                    {{ is_null($categoryId) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
            >
                Tous
                <!--<span class="text-xs opacity-75">
                    ({{ $categories->sum('tools_count') }})
                </span>-->
            </button>

            @foreach ($categories as $category)
                <button
                    wire:click="selectCategory({{ $category->id }})"
                    class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition
                        {{ $categoryId === $category->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    {{ $category->name }}
                    <!--<span class="text-xs opacity-75">
                        ({{ $category->tools_count }})
                    </span>-->
                </button>
            @endforeach
        </nav>
    </div>
    {{-- Contenu principal --}}
    <main class="max-w-7xl mx-auto p-4 sm:p-6">
                <input
        type="search"
        wire:model.live.debounce.300ms="search"
        placeholder="Rechercher un outil..."
        class="w-full max-w-xs rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
    />

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">
                {{ $categoryId ? $categories->firstWhere('id', $categoryId)?->name : 'Tous les outils' }}
            </h2>
            <span class="text-sm text-gray-500">{{ $tools->count() }} outil(s)</span>
        </div>

        @if ($tools->isEmpty())
            <div class="text-center py-16 text-gray-500">
                Aucun outil trouvé.
            </div>
        @else
            <div class="mx-auto grid max-w-6xl  grid-cols-1 gap-6 p-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($tools as $tool)
                <article class="rounded-xl bg-white p-3 shadow-lg hover:shadow-xl hover:transform hover:scale-105 duration-300 ">
                    <a href="{{route('tools.show',[$tool->id]) }}">
                    <div class="relative flex items-end overflow-hidden rounded-xl ">
                        @if ($tool->icon)
                        <img src="{{ Storage::url($tool->icon) }}" alt="{{ $tool->name }}"  class="size-52" />
                        @else
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 01-6.23-.693L5 14.5" />
                        </svg>
                        @endif
                    </div>

                    <div class="mt-1 p-2">
                        <h2 class="text-slate-700">{{ $tool->name }}</h2>
                        <p class="mt-1 text-sm text-slate-400 h-10  line-clamp-2">{{ $tool->description }}</p>

                        <div class="mt-3 flex items-end justify-between">

                            @if ($tool->contract)
                            <livewire:pastille color="{{$tool->contract->color}}" class="px-4 py-1 font-bold text-sm"> 
                                <span>Unité {{ $tool->contract->unit }}&euro;</span>
                                <span>Forfait {{ $tool->contract->flat_rate }}&euro;</span>
                            </livewire:pastille>
                            @endif
                            <p class="text-lg font-bold text-blue-500">{{ $tool->number }} dispo</p>
                            <div class="flex items-center space-x-1.5 rounded-lg bg-blue-500 px-4 py-1.5 text-white duration-100 hover:bg-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    </a>
                </article>
                @endforeach
            </div>
        @endif
    </main>
</div>