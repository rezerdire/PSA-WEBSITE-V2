<?php
use Livewire\Component;
new class extends Component {};
?>

@props([
    'tabs' => [],
])

<div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3">

        <nav
            class="
                flex items-center
                gap-2
                overflow-x-auto
                scrollbar-hide
                py-1
                sm:flex-wrap
                sm:overflow-visible
            "
            role="tablist"
            x-cloak
        >

            @foreach($tabs as $tab)
                <button
                    type="button"
                    @click="activeTab = '{{ $tab['key'] }}'"

                    :class="activeTab === '{{ $tab['key'] }}'
                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20'
                        : 'bg-gray-50 text-gray-600 hover:bg-blue-50 hover:text-blue-600 border border-gray-200 hover:border-blue-200'"

                    class="
                        group
                        relative
                        shrink-0
                        whitespace-nowrap

                        px-4 sm:px-5
                        py-2.5 sm:py-3

                        rounded-xl

                        text-xs sm:text-sm
                        font-semibold

                        transition-all
                        duration-200
                        ease-out

                        hover:-translate-y-0.5
                        active:scale-95

                        focus:outline-none
                        focus-visible:ring-2
                        focus-visible:ring-blue-400
                        focus-visible:ring-offset-2
                    "

                    role="tab"
                    :aria-selected="activeTab === '{{ $tab['key'] }}'"
                >
                    {{ $tab['label'] }}

                    {{-- Active indicator --}}
                    <span
                        x-show="activeTab === '{{ $tab['key'] }}'"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-x-50"
                        x-transition:enter-end="opacity-100 scale-x-100"
                        class="
                            absolute
                            left-1/2
                            -bottom-1
                            -translate-x-1/2
                            w-6
                            h-0.5
                            rounded-full
                            bg-blue-600
                        "
                    ></span>
                </button>
            @endforeach

        </nav>

    </div>
</div>

<style>
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
</style>