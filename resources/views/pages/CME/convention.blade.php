@extends('layouts.app')

@section('title', 'Annual Convention 2026')

@section('content')
        @vite(['resources/css/app.css', 'resources/js/app.js'])

@php
    $links = [
        [
            'key' => 'poster',
            'title' => 'Poster',
            'description' => 'Download the official convention poster',
            'icon' => 'photo',
            'href' => route('annual-convention-poster'),
        ],
        [
            'key' => 'Registration Form',
            'title' => 'Registration Form',
            'description' => 'Registration for the convention',
            'icon' => 'document-text',
            'href' => route('Event-Registration'), 
        ],
        [
            'key' => 'scientific',
            'title' => 'Scientific Program',
            'description' => 'View the full lineup of talks and sessions',
            'icon' => 'book-open',
            'href' => '#',
        ],
        [
            'key' => 'social',
            'title' => 'Social Programs',
            'description' => 'Evening events and networking activities',
            'icon' => 'sparkles',
            'href' => '#',
        ],
        // [
        //     'key' => 'tours',
        //     'title' => 'Tours & Accommodations',
        //     'description' => 'Hotel options and city tour packages',
        //     'icon' => 'paper-airplane',
        //     'href' => '#',
        // ],
        [
            'key' => 'sponsorships',
            'title' => 'Sponsors & Exhibitors',
            'description' => 'Support the convention and get featured',
            'icon' => 'user-group',
            'href' => '#',
        ],
    ];
@endphp

<div class="min-h-screen bg-white">

    {{-- Header --}}
     <x-about-us-header title="ANNUAL CONVENTION 2026" description="Jun 16, 2026 | PSA Convention | Quezon City" />
<div class="max-w-6xl mx-auto px-6 py-14">

     <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 sm:-mt-12 pb-16 sm:pb-24 ">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 mt-5">
            @foreach ($links as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="group relative bg-white rounded-xl border border-slate-300 shadow-lg shadow-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-6 sm:p-8 flex flex-col items-center text-center"
                >
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-blue-50 flex items-center justify-center mb-5 group-hover:bg-blue-600  transition-colors duration-300">
                        <x-dynamic-component
                            :component="'heroicon-o-' . $link['icon']"
                            class="w-7 h-7 sm:w-8 sm:h-8 text-[#000066] group-hover:text-white transition-colors duration-300"
                        />
                    </div>

                    <h3 class="font-display text-lg sm:text-xl text-[#000066] mb-2">
                        {{ $link['title'] }}
                    </h3>

                    <p class="text-slate-500 text-sm leading-relaxed">
                        {{ $link['description'] }}
                    </p>

                    <span class="absolute bottom-0 left-0 right-0 h-1 rounded-b-2xl bg-blue-600 scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-300"></span>
                </a>
            @endforeach
        </div>
    </div>

</div>
</div>

@endsection