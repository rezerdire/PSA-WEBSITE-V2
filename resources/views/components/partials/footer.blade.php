<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<footer class="bg-slate-900 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

      <div class="lg:col-span-2">
        <div class="flex items-center gap-1 mb-5">
              <a href="#" class="flex items-center gap-1">
        <img src="{{ asset('images/PSA_LOGO.png') }}" alt="PSA Logo" class="h-10 w-auto">
        </a>
          <div>
            <p class="font-bold text-sm uppercase">Philippine Society of <br> Anesthesiologists, INC</p>
            {{-- <p class="text-[10px] text-blue-400 uppercase tracking-widest"></p> --}}
          </div>
        </div>
        <p class="text-slate-400 text-sm leading-relaxed max-w-xs mb-6">
          Promoting safe and quality anesthesia care for the nation. A community of world-class Filipino medical professionals.
        </p>
        <div class="flex gap-3">
          <a href="https://www.facebook.com/philippinesocietyofanesthesiologists" class="w-9 h-9 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-blue-600 transition-colors" aria-label="Facebook">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
          </a>
        <a href="https://compose.mail.yahoo.com/?to=psainc_sec@yahoo.com"
   target="_blank"
   rel="noopener noreferrer"
   class="w-9 h-9 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-purple-600 transition-colors"
   aria-label="Yahoo Mail">
    <!-- SVG -->
     <svg xmlns="http://www.w3.org/2000/svg"
         fill="none"
         viewBox="0 0 24 24"
         stroke-width="2"
         stroke="currentColor"
         class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5H4.5A2.25 2.25 0 0 0 2.25 6.75m19.5 0-9.75 6.75L2.25 6.75" />
    </svg>
</a>
        </div>
        
      </div>

      <div>
        <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Quick Links</p>
        <ul class="space-y-2.5">
          
          <li><a href="{{ route('Office-and-board') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Officers &amp; Board</a></li>
          <li><a href="{{ route('Chapter-Presidents') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Chapter Presidents</a></li>
          <li><a href="{{ route('Legacy') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Past Presidents</a></li>
          <li><a href="{{ route('pja') }}" class="text-sm text-slate-400 hover:text-white transition-colors">PJA Journal</a></li>
        </ul>
      </div>

      <div>
        <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Events</p>
        <ul class="space-y-2.5">
          <li><a href="{{ route('convention') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Annual Convention 2026</a></li>
          <li><a href="{{ route('Gallery') }}" class="text-sm text-slate-400 hover:text-white transition-colors">Gallery</a></li>
        </ul>
      </div>

    </div>

    <div class="border-t border-slate-800 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-xs text-slate-500">© {{ date('Y') }} Philippine Society of Anesthesiologists (PSA). All Rights Reserved.</p> 
    </div>
  </div>
</footer>