@vite(['resources/css/app.css', 'resources/js/app.js'])

@extends('layouts.app')

@section('title', 'Midyear Convention 2026')

@section('content')

<div x-data="{ activeTab: 'poster-rates' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Annual Convention 2026" description="November 25-27, 2026 | Mariott Grand Ballroom | Pasay City" />

<div class = "flex justify-center mt-2">
    <a href = "{{ route('Event-Registration') }}" class ="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-semibold text-sm sm:text-base rounded-xl hover:bg-blue-700 transition-all shadow-md shadow-blue-200 hover:-translate-y-0.5 " >Click Here to Register </a>
</div>
    <x-about-us-content :panels="[
        ['key' => 'poster-rates',   'image' => '/annual-convention/58th Annual Convention Rates.png',  'alt' => 'POSTER&RATES',
        'link' => route('Event-Registration'), ],

    ]" />
</div>
@endsection