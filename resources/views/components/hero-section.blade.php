<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<section class="relative min-h-screen pt-16 flex items-center overflow-hidden bg-white">
  {{-- Decorative shapes bg --}}
  <div class="absolute inset-0 pointer-events-none overflow-hidden">
    <div class="absolute -top-20 -right-20 w-[300px] h-[300px] sm:-top-32 sm:-right-32 sm:w-[600px] sm:h-[600px] bg-blue-50 rounded-full opacity-60"></div>  {{-- right circle --}}
    <div class="absolute bottom-0 -left-16 w-[200px] h-[200px] sm:-left-24 sm:w-[400px] sm:h-[400px] bg-blue-50 rounded-full opacity-40"></div> {{-- left bottom --}}
    <svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg"><defs> <pattern id="dots" x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.5" fill="#2563eb"/></pattern></defs><rect width="100%" height="100%" fill="url(#dots)"/></svg>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 lg:py-32 w-full">
    <div class="grid lg:grid-cols-2 gap-10 sm:gap-16 items-center">

      {{-- Left --}}
      <div>
        {{-- COMMENT OUT IF EVER THERE WILL BE A REGISTRATION FOR MEMBERSHIP --}}
        {{-- <div class="au1 inline-flex items-center gap-2 bg-blue-50 border border-blue-100 text-blue-700 text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-full mb-6">
          <span class="pulsedot w-1.5 h-1.5 bg-blue-600 rounded-full"></span>
          Now Accepting Members · 2026
        </div> --}}   

        <h1 class="au2 font-display text-3xl sm:text-5xl lg:text-6xl xl:text-7xl leading-[1.15] sm:leading-[1.08] text-slate-900 mb-4 sm:mb-6">
          Philippine Society<br>of <em class="text-blue-600 not-italic">Anesthesiologists</em>
        </h1>

        <p class="au3 text-slate-500 text-base sm:text-lg leading-relaxed max-w-lg mb-6 sm:mb-10">
          Promoting safe and quality anesthesia care across the nation. A community of world-class Filipino anesthesiologists driven by excellence.
        </p>

        <div class="au3 flex flex-wrap gap-3 sm:gap-4">
          <a href="#" class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-semibold text-sm sm:text-base rounded-xl hover:bg-blue-700 transition-all shadow-md shadow-blue-200 hover:-translate-y-0.5">
           Membership <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right-icon lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </a>
        
          <a href="#recent-events" class="px-5 sm:px-6 py-2.5 sm:py-3 bg-white text-slate-700 font-semibold text-sm sm:text-base rounded-xl border border-slate-200 hover:border-blue-300 hover:text-blue-600 transition-all">
            Check our events
          </a>
        </div>

        {{-- Stats --}}
    {{-- Stats --}}
        <div class="au3 mt-10 sm:mt-14 grid grid-cols-3 gap-2 sm:gap-4 border-t border-slate-100 pt-6 sm:pt-8">
          <div class="rounded-xl sm:rounded-2xl border border-slate-100 bg-slate-100 px-2 py-3 sm:px-5 sm:py-5 text-center sm:text-left">
            <p class="font-display text-lg sm:text-2xl md:text-3xl text-slate-900">70+</p>
            <p class="text-[9px] sm:text-xs text-slate-500 uppercase tracking-wider mt-1 leading-tight">Years of Service</p>
          </div>
          <div class="rounded-xl sm:rounded-2xl border border-slate-100 bg-slate-100 px-2 py-3 sm:px-5 sm:py-5 text-center sm:text-left">
            <p class="font-display text-lg sm:text-2xl md:text-3xl text-slate-900">6,000+</p>
            <p class="text-[9px] sm:text-xs text-slate-500 uppercase tracking-wider mt-1 leading-tight">Members Nationwide</p>
          </div>
          <div class="rounded-xl sm:rounded-2xl border border-slate-100 bg-slate-100 px-2 py-3 sm:px-5 sm:py-5 text-center sm:text-left">
            <p class="font-display text-lg sm:text-2xl md:text-3xl text-slate-900">32</p>
            <p class="text-[9px] sm:text-xs text-slate-500 uppercase tracking-wider mt-1 leading-tight">Regional Chapters</p>
          </div>
        </div>
      </div>

    </div>
  </div>

 
</section>