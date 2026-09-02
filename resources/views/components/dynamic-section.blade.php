<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

@props([
    'title' => 'Sim Wars',
    'description' => '',
    'youtube' => '',
])

<section id="sim-wars" class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="flex items-center justify-center gap-3 mb-3">
                <span class="w-10 h-1 bg-blue-600 rounded-full"></span>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                    Highlights
                </p>
            </div>

            <h2 class="font-serif text-4xl lg:text-5xl text-slate-900">
                {{ $title }}
            </h2>

            @if ($description)
                <p class="mt-4 max-w-2xl mx-auto text-slate-600 text-base lg:text-lg">
                    {{ $description }}
                </p>
            @endif
        </div>

        <div class="max-w-7xl mx-auto">
            <div class="border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="relative rounded-2xl overflow-hidden bg-black aspect-video">
                    <iframe
                        class="w-full h-full"
                        src="https://www.youtube.com/embed/{{ $youtube }}"
                        title="{{ $title }}"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>

    </div>
</section>  