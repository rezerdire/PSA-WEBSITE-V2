<?php
use Livewire\Component;
new class extends Component {};
?>

@props([
    'eyebrow' => 'Philippine Society of Anesthesiologists',
    'title',
    'description' => null,
])

<section class="mt-5 relative bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 py-10 overflow-hidden">

    <div class="relative max-w-6xl mx-auto px-3 text-center">
        <p class="text-blue-200 text-xs font-semibold tracking-[0.2em] uppercase mt-10">
            {{ $eyebrow }}
        </p>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight">
            {{ $title }}
        </h1>
        @if($description)
            <p class="mt-1 text-blue-100 text-base max-w-xl mx-auto leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>
</section>