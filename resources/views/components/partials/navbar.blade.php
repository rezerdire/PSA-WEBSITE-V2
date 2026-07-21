<?php

use Livewire\Component;

new class extends Component
{
    
};
?>

<nav class="fixed top-0 inset-x-0 z-50 bg-white/95 backdrop-blur border-b border-slate-100 shadow-sm" x-data="{ mobileOpen: false }">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">

      {{-- Logo --}}
      <a href="{{ route('home') }}" class="flex items-center gap-1">
            <img src="{{ asset('Images/PSA_LOGO.png') }}" alt="PSA Logo" class="h-12 w-10 w-auto">
        <div class="leading-tight">
          <span class="block text-sm font-bold text-slate-900 tracking-tight uppercase">Philippine Society of <br> Anesthesiologists, Inc</span>
        </div>
      </a>

      {{-- Desktop Nav --}}
      <div class="hidden lg:flex items-center gap-0.5">
        <a wire:navigate href="{{ route('home') }}" class="nav-link px-3 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 rounded-md hover:bg-blue-50 transition-colors">Home</a>

        {{-- About Us Dropdown --}}
        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
          <button class="nav-link flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 rounded-md hover:bg-blue-50 transition-colors">
            About Us
            <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
               class="absolute top-full left-0 mt-1 w-52 bg-white border border-slate-100 rounded-xl shadow-xl shadow-slate-200/60 py-1 z-50">
            <a wire:navigate href="{{ route('Office-and-board') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">Officers &amp; Board</a>
            <a wire:navigate href="{{ route('SubSpecialty-SIG') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">Subspecialty &amp; SIG</a>
            <a wire:navigate href="{{ route('Chapter-Presidents') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">Chapter Presidents</a>
            <a wire:navigate href="{{ route('Legacy') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">Legacy</a>
            <a  href="{{ route('psa-hymn') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">PSA Hymn</a>
          </div>
        </div>

        {{-- CME Activities Dropdown --}}
        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
          <button class="nav-link flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 rounded-md hover:bg-blue-50 transition-colors">
            CME Activities
            <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
               class="absolute top-full left-0 mt-1 w-52 bg-white border border-slate-100 rounded-xl shadow-xl shadow-slate-200/60 py-1 z-50">
            <a href="{{ route('convention') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">Annual Convention 2026</a>
            <a href="{{ route('mid-year-convention') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">Midyear Convention</a>

            <a href="{{ route('pja') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:text-blue-600 hover:bg-blue-50 transition-colors">PJA</a>
          </div>
        </div>

        {{-- Gallery Dropdown --}}
        <a wire:navigate href="{{ route('Gallery') }}" class="nav-link px-3 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 rounded-md hover:bg-blue-50 transition-colors">Gallery</a>
        <a wire:navigate href="{{ route('convention') }}" class="nav-link px-3 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 rounded-md hover:bg-blue-50 transition-colors">Annual Convention 2026</a>
        <a wire:navigate href="{{ route('info-scan-qr') }}" class="nav-link px-3 py-2 text-sm font-medium text-slate-700 hover:text-blue-600 rounded-md hover:bg-blue-50 transition-colors">ID Scanner</a>

        {{-- <a wire:navigate href="{{ route('Login') }}" class="ml-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">Login </a> --}}

        {{-- <a href="{{ route('Membership') }}" class="ml-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">Membership </a> --}}
      </div>

      {{-- Mobile toggle --}}
      <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-md text-slate-600 hover:bg-slate-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- Mobile menu --}}
    {{-- Mobile menu --}}
<div x-show="mobileOpen" x-transition class="lg:hidden border-t border-slate-100 py-3">
  <a href="{{ route('home') }}" class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Home</a>

  {{-- About Us accordion --}}
  <div x-data="{ aboutOpen: false }">
    <button @click="aboutOpen = !aboutOpen" class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
      About Us
      <svg class="w-3 h-3 opacity-50 transition-transform duration-200" :class="{ 'rotate-180': aboutOpen }" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="aboutOpen" x-transition class="pl-4">
      <a wire:navigate href="{{ route('Office-and-board') }}" class="block px-4 py-2 text-sm text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Officers &amp; Board</a>
      <a wire:navigate href="{{ route('SubSpecialty-SIG') }}" class="block px-4 py-2 text-sm text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Subspecialty &amp; SIG</a>
      <a wire:navigate href="{{ route('Chapter-Presidents') }}" class="block px-4 py-2 text-sm text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Chapter Presidents</a>
      <a wire:navigate href="{{ route('Legacy') }}" class="block px-4 py-2 text-sm text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Past Presidents</a>
      <a wire:navigate href="{{ route('psa-hymn') }}" class="block px-4 py-2 text-sm text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">PSA Hymn</a>

    </div>
  </div>
{{-- end about us section --}}

{{-- CME section --}}
<div x-data="{ aboutOpen: false }">
    <button @click="aboutOpen = !aboutOpen" class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
      CME Activities
      <svg class="w-3 h-3 opacity-50 transition-transform duration-200" :class="{ 'rotate-180': aboutOpen }" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="aboutOpen" x-transition class="pl-4">
      <a wire:navigate href="{{ route('convention') }}" class="block px-4 py-2 text-sm text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Annual Convention 2026</a>
      <a wire:navigate href="{{ route('pja') }}" class="block px-4 py-2 text-sm text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg">PJA</a>
 
    </div>
  </div>


  <a wire:navigate href="{{ route('Gallery') }}" class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Gallery</a>
  <a wire:navigate href="{{ route('info-scan-qr') }}" class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Profile Scanner</a>
  {{-- <a wire:navigate href="{{ route('Login') }}" class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Login</a> --}}

</div>
  </div>
</nav>