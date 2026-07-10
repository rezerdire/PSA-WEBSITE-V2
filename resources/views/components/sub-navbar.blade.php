<?php
use Livewire\Component;
new class extends Component {};
?>

@props([
    'tabs' => [],
])

<div class="sticky top-0 z-30 bg-white ">
    <div class="max-w-6xl mx-auto px-6">
        <nav class="flex gap-0 overflow-x-auto" role="tablist" x-cloack>

            @foreach($tabs as $tab)
                <button @click="activeTab = '{{ $tab['key'] }}'" :class="activeTab === '{{ $tab['key'] }}'
                        // condition and design
                        ? 'border-b-2 border-blue-600 text-blue-700 font-semibold'
                        // met the condition
                        : 'text-gray-500 hover:text-blue-600'"
                        {{-- default design  --}}
                    class="px-6 py-4 text-sm whitespace-nowrap transition-colors duration-150 focus:outline-none" role="tab"

                    :aria-selected="activeTab === '{{ $tab['key'] }}'">
                    {{ $tab['label'] }}
                </button>   
            @endforeach

        </nav>
    </div>
</div>