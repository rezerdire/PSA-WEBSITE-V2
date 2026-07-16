<?php
use Livewire\Component;
new class extends Component {};
?>

@props([
    'tabs' => [],
])

<div class="sticky top-0 z-30 bg-white border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-6 py-4">


        <nav class="flex flex-wrap gap-2" role="tablist" x-cloak>

            @foreach($tabs as $tab)
                <button
                    @click="activeTab = '{{ $tab['key'] }}'"
                    :class="activeTab === '{{ $tab['key'] }}'
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900'"
                    class="px-5 py-3 rounded-full text-sm font-bold whitespace-nowrap transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-300"
                    role="tab"
                    :aria-selected="activeTab === '{{ $tab['key'] }}'"
                >
                    {{ $tab['label'] }}
                </button>
            @endforeach

        </nav>
    </div>
</div>