<?php


use App\Models\GalleryEvent;
use App\Models\GalleryImage;
use Livewire\Component;

new class extends Component
{
    public ?GalleryEvent $featuredEvent = null;
    public $previewImages;

    public function mount(): void
    {
        $this->featuredEvent = GalleryEvent::with('days')->latest()->first();

        if ($this->featuredEvent) {
            $dayIds = $this->featuredEvent->days->pluck('id');

            $this->previewImages = GalleryImage::with('category')
                ->whereHas('category', fn ($q) => $q->whereIn('gallery_day_id', $dayIds))
                ->inRandomOrder()
                ->limit(6)
                ->get();
        } else {
            $this->previewImages = collect();
        }
    }

    public function render()
    {
        return <<<'BLADE'
            <div>@include('components.recent-events-view')</div>
        BLADE;
    }
};
?>


<section id ="recent-events" class="py-16 sm:py-24 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 
    {{-- Section header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 sm:mb-12 gap-3 sm:gap-4">
      <div>
        <p class="slabel text-xs font-bold uppercase tracking-widest text-blue-600 mb-2 sm:mb-3">Events</p>
        <h2 class="font-display text-2xl sm:text-4xl lg:text-5xl text-slate-900">Recent Events</h2>
      </div>
      <a href="#" class="text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
        View all events <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-icon lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </a>
    </div>
 
    {{-- ── Event cards ── --}}
    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5 mb-12 sm:mb-16">
 
        {{-- card1 --}}
      <a href="https://aca2025manila.org/" class="block bg-white rounded-2xl overflow-hidden card border border-slate-200 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 hover:border-blue-200 hover:ring-1 hover:ring-blue-200">
        <div class="relative w-full aspect-[16/10] overflow-hidden bg-slate-100">
          <img src="{{ asset('images/event-cover-photo/ACA-2025-CP.png') }}"
               alt="ASEAN Congress of Anesthesiologists 2025"
               class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
          <span class="absolute top-3 left-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-blue-100 text-blue-700">
            International
          </span>
          <span class="absolute top-3 right-3 text-[10px] font-medium text-blue-700 bg-white/90 px-2 py-1 rounded-full">Featured</span>
        </div>
        <div class="p-5 sm:p-6">
          <h3 class="font-display text-lg sm:text-xl text-slate-900 mb-2.5 sm:mb-3 leading-tight">
            ASEAN Congress of Anesthesiologists 2025
          </h3>
          <div class="space-y-1.5 sm:space-y-2 text-xs text-slate-500 mb-3 sm:mb-4">
            <div class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              October 23–25, 2025
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Marriott Grand Ballroom, Pasay City
            </div>
          </div>
          <span class="text-sm font-semibold text-blue-600 flex items-center gap-1">
            Learn More <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-icon lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </span>
        </div>
      </a>
 
      {{-- card2 --}}
      <a href="#" class="block bg-white rounded-2xl overflow-hidden card border border-slate-200 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 hover:border-blue-200 hover:ring-1 hover:ring-blue-200">
        <div class="relative w-full aspect-[16/10] overflow-hidden bg-slate-100">
          <img src="{{ asset('images/event-cover-photo/PSARP-CP.png') }}"
               alt="PSARP Annual Convention"
               class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
          <span class="absolute top-3 left-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-slate-100 text-slate-600">
            National
          </span>
        </div>
        <div class="p-5 sm:p-6">
          <h3 class="font-display text-lg sm:text-xl text-slate-900 mb-2.5 sm:mb-3 leading-tight">
            PSARP Annual Convention
          </h3>
          <div class="space-y-1.5 sm:space-y-2 text-xs text-slate-500 mb-3 sm:mb-4">
            <div class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              2025
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Philippines
            </div>
          </div>
          <span class="text-sm font-semibold text-blue-600 flex items-center gap-1">
            Learn More <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-icon lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </span>
        </div>
      </a>

      {{-- card3 --}}
      <a href="https://psa-inc.org/sim-wars" class="block bg-white rounded-2xl overflow-hidden card border border-slate-200 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 hover:border-blue-200 hover:ring-1 hover:ring-blue-200">
        <div class="relative w-full aspect-[16/10] overflow-hidden bg-slate-100">
          <img src="{{ asset('images/event-cover-photo/SIMWARS-CP.jpg') }}"
               alt="SIM Wars Trilogy"
               class="w-full h-full object-cover" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
          <span class="absolute top-3 left-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-green-100 text-green-700">
            Upcoming
          </span>
        </div>
        <div class="p-5 sm:p-6">
          <h3 class="font-display text-lg sm:text-xl text-slate-900 mb-2.5 sm:mb-3 leading-tight">
            SIM Wars Trilogy
          </h3>
          <div class="space-y-1.5 sm:space-y-2 text-xs text-slate-500 mb-3 sm:mb-4">
            <div class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
               Aug 9, 2026 - Part 1 (Elimination Round)
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            Aesculap Academy B. Braun Philippines, Bonifacio Global City        
            </div>
          </div>
          <span class="text-sm font-semibold text-blue-600 flex items-center gap-1">
            Learn More <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-icon lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </span>
        </div>
      </a>
 
    </div>
 
  </div>
</section>