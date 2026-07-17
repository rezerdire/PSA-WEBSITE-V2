<?php
use Livewire\Component;
new class extends Component
{
   // 
};
?>

@props([
    'panels' => [],
])

<div class="max-w-6xl mx-auto px-6 py-14">
    @foreach($panels as $panel)
        <div
            x-show="activeTab === '{{ $panel['key'] }}'"
            @if(!$loop->first) 
            x-cloak {{-- hide the raw structure if it loads--}}
            @endif
            x-transition:enter="transition ease-out duration-200" 
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0">

            @if(!empty($panel['title']))
                <h2 class="text-center text-2xl font-bold text-gray-800 mb-2">
                    {{ $panel['title'] }}
                </h2>
            @endif

            @if(!empty($panel['subtitle']))
                <p class="text-center text-sm text-gray-500 mb-10">
                    {{ $panel['subtitle'] }}
                </p>
            @endif

            {{-- YT --}}
            @if(!empty($panel['youtube']))
                <div class="w-full max-w-4xl mx-auto mt-12 px-6">
                    <div x-data="{ playing: false }" class="relative w-full aspect-video">
                        <div
                            x-show="!playing"
                            @click="playing = true"
                            class="absolute inset-0 cursor-pointer rounded-xl overflow-hidden">
                            <img
                                src="https://img.youtube.com/vi/{{ $panel['youtube'] }}/maxresdefault.jpg"
                                alt="{{ $panel['title'] ?? 'Video' }}"
                                class="w-full h-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-16 h-12 bg-red-600 rounded-xl flex items-center justify-center shadow-lg hover:bg-red-500 transition-colors duration-150">
                                    <svg class="w-6 h-6 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <iframe
                            x-show="playing"
                            x-cloak
                            src="https://www.youtube.com/embed/{{ $panel['youtube'] }}"
                            title="{{ $panel['title'] ?? 'Video' }}"
                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                            class="absolute inset-0 w-full h-full border-0 rounded-xl">
                        </iframe>

                    </div>
                </div>

            @elseif(!empty($panel['image']))
                <img
                    src="{{ $panel['image'] }}"
                    alt="{{ $panel['alt'] ?? '' }}"
                    class="w-full h-auto object-cover">
            @endif

        </div>
    @endforeach
</div>