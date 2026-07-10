<?php
use Livewire\Component;
new class extends Component {};
?>

@props([
    'eyebrow' => 'Philippine Society of Anesthesiologists',
    'title',
    'description' => null,
])

<section class="relative bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 py-16 overflow-hidden">
    <div class="absolute inset-0 opacity-10 pointer-events-none"
         style="background-image: radial-gradient(circle at 70% 50%, #fff 0%, transparent 60%)">
    </div>

    <div class="relative max-w-6xl mx-auto px-6 text-center">
        <p class="text-blue-200 text-xs font-semibold tracking-[0.2em] uppercase mb-3 mt-10">
            {{ $eyebrow }}
        </p>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight">
            {{ $title }}
        </h1>
        @if($description)
            <p class="mt-4 text-blue-100 text-base max-w-xl mx-auto leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>
</section>