<?php


use App\Models\GalleryEvent;
use App\Models\GalleryImage;
use Livewire\Component;

new class extends Component
{
}
?>

<section id="recent-events" class="py-16 sm:py-24 bg-white"
         x-data="{ index: 0, visible: 3, total: 4 }">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Section header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 sm:mb-12 gap-3 sm:gap-4">
      <div>
        <p class="slabel text-xs font-bold uppercase tracking-widest text-blue-600 mb-2 sm:mb-3">Events</p>
        <h2 class="font-display text-2xl sm:text-4xl lg:text-5xl text-slate-900">Recent Events</h2>
      </div>
      <a href="{{ route('recent-event-list') }}" class="text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition-colors">
        View all events <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-icon lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </a>
    </div>

    {{-- ── Event cards (slides one card per click) ── --}}
    <div class="relative overflow-hidden mb-8 sm:mb-10">
      <div
        class="flex transition-transform duration-500 ease-in-out gap-4 sm:gap-5"
        :style="`transform: translateX(calc(-${index} * (100% / ${visible} + ${(16)}px / ${visible})))`"
      >

        {{-- card1 --}}
                <a href="#" class="block bg-white rounded-2xl overflow-hidden card border border-slate-200 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 hover:border-blue-200 hover:ring-1 hover:ring-blue-200 flex-shrink-0 w-full sm:w-[calc((100%-1rem)/2)] md:w-[calc((100%-2*1.25rem)/3)]">
          <div class="relative w-full aspect-[16/10] overflow-hidden bg-slate-100">
            <img src="{{ asset('images/researchforum2026.jpg') }}"
                 alt="PSA Research Forum 2026"
                 class="w-full h-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            <span class="absolute top-3 left-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-green-100 text-green-700">
              Call for Abstracts
            </span>
          </div>
          <div class="p-5 sm:p-6">
            <h3 class="font-display text-lg sm:text-xl text-slate-900 mb-2.5 sm:mb-3 leading-tight">
              PSA Research Forum 2026
            </h3>
            <div class="space-y-1.5 sm:space-y-2 text-xs text-slate-500 mb-3 sm:mb-4">
              <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Deadline: August 20, 2026
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

      
        {{-- card2: SIM Wars --}}
        <a href="{{ route('sim-wars') }}" class="block bg-white rounded-2xl overflow-hidden card border border-slate-200 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 hover:border-blue-200 hover:ring-1 hover:ring-blue-200 flex-shrink-0 w-full sm:w-[calc((100%-1rem)/2)] md:w-[calc((100%-2*1.25rem)/3)]">
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

        {{-- card3: Interesting Case --}}
        <a href="{{ route('Interesting-Case') }}" class="block bg-white rounded-2xl overflow-hidden card border border-slate-200 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 hover:border-blue-200 hover:ring-1 hover:ring-blue-200 flex-shrink-0 w-full sm:w-[calc((100%-1rem)/2)] md:w-[calc((100%-2*1.25rem)/3)]">
          <div class="relative w-full aspect-[16/10] overflow-hidden bg-slate-100">
            <img src="{{ asset('images/InterestingCase.png') }}"
                 alt="PSA Interesting Case Competition 2026"
                 class="w-full h-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            <span class="absolute top-3 left-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-green-100 text-green-700">
              Upcoming
            </span>
          </div>
          <div class="p-5 sm:p-6">
            <h3 class="font-display text-lg sm:text-xl text-slate-900 mb-2.5 sm:mb-3 leading-tight">
              PSA Interesting Case Competition 2026
            </h3>
            <div class="space-y-1.5 sm:space-y-2 text-xs text-slate-500 mb-3 sm:mb-4">
              <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Deadline: August 28, 2026
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

        {{-- card4 --}}

          <a href="#" class="block bg-white rounded-2xl overflow-hidden card border border-slate-200 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 hover:border-blue-200 hover:ring-1 hover:ring-blue-200 flex-shrink-0 w-full sm:w-[calc((100%-1rem)/2)] md:w-[calc((100%-2*1.25rem)/3)]">
          <div class="relative w-full aspect-[16/10] overflow-hidden bg-slate-100">
            <img src="{{ asset('images/event-cover-photo/PSARP-CP.png') }}"
                 alt="PSA Review Program (PSARP)"
                 class="w-full h-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            <span class="absolute top-3 left-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-green-100 text-green-700">
              ON GOING
            </span>
          </div>
          <div class="p-5 sm:p-6">
            <h3 class="font-display text-lg sm:text-xl text-slate-900 mb-2.5 sm:mb-3 leading-tight">
              PSA Review Program (PSARP)
            </h3>
            <div class="space-y-1.5 sm:space-y-2 text-xs text-slate-500 mb-3 sm:mb-4">
              <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                2026
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

      </div>
    </div>

    {{-- pagination controls --}}
    <div class="flex items-center justify-center gap-5">

      <button
        @click="index = index > 0 ? index - 1 : index"
        :disabled="index === 0"
        :class="index === 0 ? 'opacity-40 cursor-not-allowed' : 'hover:border-blue-300 hover:text-blue-600 hover:shadow-md'"
        class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition-all"
        aria-label="Previous event"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m15 18-6-6 6-6"/>
        </svg>
      </button>

      <div class="flex items-center gap-2">
        <template x-for="i in (total - visible + 1)" :key="i">
          <button
            @click="index = i - 1"
            :class="index === i - 1 ? 'w-8 bg-blue-600' : 'w-2 bg-slate-300'"
            class="h-2 rounded-full transition-all duration-300"
            :aria-label="`Go to card ${i}`"
          ></button>
        </template>
      </div>

      <button
        @click="index = index < (total - visible) ? index + 1 : index"
        :disabled="index === (total - visible)"
        :class="index === (total - visible) ? 'opacity-40 cursor-not-allowed' : 'hover:border-blue-300 hover:text-blue-600 hover:shadow-md'"
        class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition-all"
        aria-label="Next event"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m9 18 6-6-6-6"/>
        </svg>
      </button>

    </div>

  </div>
</section>