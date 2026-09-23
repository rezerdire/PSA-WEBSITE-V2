<?php

use Livewire\Component;

new class extends Component
{
    public string $videoUrl = 'video/simwarsvideo.mp4';
    public ?string $mobileVideoUrl = null;
    public ?string $poster = null;
    public string $title = 'Sim Wars';
    public bool $forcePortrait = false; // true = always portrait, even on desktop
};
?>

<section class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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
        </div>
        
<div class="{{ $forcePortrait ? 'max-w-sm' : 'max-w-md sm:max-w-2xl lg:max-w-7xl' }} mx-auto">
            <div class="border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="relative rounded-2xl overflow-hidden bg-black {{ $forcePortrait ? 'aspect-[9/16]' : 'aspect-[9/16] sm:aspect-video' }}">
                    <video
                        class="w-full h-full object-contain"
                        controls
                        preload="metadata"
                        @if($poster) poster="{{ asset($poster) }}" @endif
                    >
                        <source src="{{ asset($mobileVideoUrl ?? $videoUrl) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>

    </div>
</section>