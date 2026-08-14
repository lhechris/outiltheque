<?php
use Livewire\Component;

new class extends Component {
    public function getColorClass($color): string
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
} 
?>
@props([
    'color',
])
<div {{ $attributes->merge(['class' => 'rounded-full '.$this->getColorClass($color)]) }} >    
    {{$slot}}
</div>