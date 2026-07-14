<?php

use Livewire\Component;
use App\Models\GalleryImage;

new class extends Component
{
    public $featured;
    public $smallTiles;

    public function mount()
    {
        $randomImages = GalleryImage::with('category')
            ->whereNotNull('large_path')
            ->inRandomOrder()
            ->get()
            ->unique('gallery_category_id')
            ->take(6)
            ->values();

        $this->featured = $randomImages->first();
        $this->smallTiles = $randomImages->slice(1, 5)->values();
    }
};
?>
<section id="recent-events" class="py-12 md:py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div x-data="{ lightbox: false, activeSrc: '', activeAlt: '' }">

      <div class="flex items-center justify-between mb-4 md:mb-6">
        <p class="slabel text-[11px] md:text-xs font-bold uppercase tracking-widest text-blue-600">ACA 2025 Gallery</p>
        <a href="{{ route('Gallery') }}" class="text-xs md:text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
          View More Photos
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>

      @if ($featured)
      <div class="grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3">

        <div class="gal-item col-span-2 row-span-2 md:col-span-2 md:row-span-2 relative rounded-xl md:rounded-2xl overflow-hidden bg-slate-100 cursor-pointer transition-all duration-300 ease-out hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl" style="aspect-ratio:16/9"
          @click="lightbox = true; activeSrc = '{{ asset($featured->large_path) }}'; activeAlt = '{{ $featured->category->name }}'">
          <img src="{{ asset($featured->large_path) }}"
               alt="{{ $featured->category->name }}" class="w-full h-full object-cover" loading="lazy">
          <div class="overlay absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent flex items-end p-3 md:p-5">
            <p class="text-white font-semibold text-xs md:text-sm">{{ $featured->category->name }}</p>
          </div>
        </div>

        @foreach ($smallTiles as $tile)
        <div class="gal-item relative rounded-xl md:rounded-2xl overflow-hidden bg-slate-100 cursor-pointer transition-all duration-300 ease-out hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl" style="aspect-ratio:16/9"
          @click="lightbox = true; activeSrc = '{{ asset($tile->large_path) }}'; activeAlt = '{{ $tile->category->name }}'">
          <img src="{{ asset($tile->large_path) }}"
               alt="{{ $tile->category->name }}" class="w-full h-full object-cover" loading="lazy">
          <div class="overlay absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent flex items-end p-2.5 md:p-4">
            <p class="text-white font-semibold text-[11px] md:text-xs">{{ $tile->category->name }}</p>
          </div>
        </div>
        @endforeach

      </div>

      <div class="flex justify-center mt-6 md:mt-8">
        <a href="{{ route('Gallery') }}"
           class="inline-flex items-center gap-2 px-5 md:px-6 py-2.5 md:py-3 rounded-full bg-blue-600 text-white font-semibold text-xs md:text-sm hover:bg-blue-700 transition-colors shadow-sm">
          View More Photos
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>
      @else
      <p class="text-slate-400 text-sm">No photos available yet.</p>
      @endif

      <div
        x-show="lightbox"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/90 backdrop-blur-sm p-3 md:p-4"
        @click.self="lightbox = false"
        @keydown.escape.window="lightbox = false"
        style="display: none;"
      >
        <div class="relative max-w-5xl w-full">
          <button
            @click="lightbox = false"
            class="absolute -top-9 md:-top-10 right-0 text-white/70 hover:text-white transition-colors flex items-center gap-1.5 text-xs md:text-sm font-medium"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
          <img :src="activeSrc" :alt="activeAlt"
               class="w-full rounded-xl md:rounded-2xl shadow-2xl object-contain max-h-[75vh] md:max-h-[80vh]">
          <p class="text-center text-white/70 text-xs md:text-sm mt-3 md:mt-4" x-text="activeAlt"></p>
        </div>
      </div>

    </div>
  </div>
</section>