<?php

use Livewire\Component;

new class extends Component
{
    public string $videoUrl = 'video/simwarsvideo.mp4';
    public ?string $mobileVideoUrl = null;
    public ?string $poster = null;
    public string $title = 'Sim Wars';
    public string $eyebrow = 'Highlights';
    public ?string $description = null;
    public ?string $sectionId = null;
    public bool $forcePortrait = false; // true = always portrait, even on desktop
    public bool $forceLandscape = false; // true = use a landscape frame at every breakpoint
};
?>

 
 

<section @if($sectionId) id="{{ $sectionId }}" @endif class="scroll-mt-24 bg-slate-50 py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
            <div class="mb-3 flex items-center justify-center gap-3">
                <span class="h-1 w-10 rounded-full bg-blue-600"></span>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                    {{ $eyebrow }}
                </p>
            </div>
            <h2 class="font-serif text-4xl text-slate-900 lg:text-5xl">
                {{ $title }}
            </h2>
            @if($description)
                <p class="mx-auto mt-5 max-w-3xl text-sm leading-7 text-slate-600 sm:text-base">
                    {{ $description }}
                </p>
            @endif
        </div>

        <div class="{{ $forcePortrait ? 'max-w-sm' : 'max-w-md sm:max-w-2xl lg:max-w-7xl' }} mx-auto">
            <div class="border border-slate-100 transition-all duration-300 hover:shadow-lg">
                <div class="relative overflow-hidden rounded-2xl bg-black {{ $forcePortrait ? 'aspect-[9/16]' : ($forceLandscape ? 'aspect-video' : 'aspect-[9/16] sm:aspect-video') }}">
                    <video
                        class="h-full w-full object-contain"
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