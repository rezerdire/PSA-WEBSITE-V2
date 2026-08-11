@php
    $events = [
        [
            'title' => 'PSA Research Forum 2026',
            'image' => 'images/researchforum2026.jpg',
            'badge' => 'Call for Abstracts',
            'date' => 'Deadline: August 20, 2026',
            'location' => 'Philippines',
            'url' => '#',
        ],
        [
            'title' => 'SIM Wars Trilogy',
            'image' => 'images/event-cover-photo/SIMWARS-CP.jpg',
            'badge' => 'Upcoming',
            'date' => 'Aug 9, 2026 - Part 1 (Elimination Round)',
            'location' => 'Aesculap Academy B. Braun Philippines, Bonifacio Global City',
            'route' => 'sim-wars',
        ],
        [
            'title' => 'PSA Interesting Case Competition 2026',
            'image' => 'images/InterestingCase.png',
            'badge' => 'Upcoming',
            'date' => 'Deadline: August 28, 2026',
            'location' => 'Philippines',
            'route' => 'Interesting-Case',
        ],
        [
            'title' => 'PSA Review Program (PSARP)',
            'image' => 'images/event-cover-photo/PSARP-CP.png',
            'badge' => 'ON GOING',
            'date' => '2026',
            'location' => 'Philippines',
            'url' => '#',
        ],
    ];
@endphp

@vite(['resources/css/app.css', 'resources/js/app.js'])

@section('title', 'PSA Events')
@extends('layouts.app')

@section('content')

    <section id="recent-events" class="py-16 sm:py-24 bg-slate-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 sm:mb-14 gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-8 h-px bg-blue-600"></span>

                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">
                            Events
                        </p>
                    </div>

                    <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl text-slate-900 leading-tight">
                        Recent Events
                    </h2>

                    <p class="mt-3 text-sm sm:text-base text-slate-500 max-w-xl">
                        Stay updated with the latest programs, competitions, forums,
                        and activities of the Philippine Society of Anesthesiologists.
                    </p>
                </div>

                <div class="hidden sm:flex items-center gap-2 text-sm font-medium text-slate-400">
                    <span>{{ count($events) }}</span>
                    <span>Events</span>
                </div>
            </div>


            {{-- Event Cards --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                @foreach ($events as $event)
                    <a href="{{ isset($event['route']) ? route($event['route']) : $event['url'] }}"
                        class="group relative flex flex-col overflow-hidden rounded-2xl bg-white border border-slate-200
                           shadow-sm hover:shadow-xl hover:-translate-y-1
                           transition-all duration-300">

                        {{-- Image --}}
                        <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">

                            <img src="{{ asset($event['image']) }}" alt="{{ $event['title'] }}"
                                class="w-full h-full object-cover
                                   group-hover:scale-105
                                   transition-transform duration-700 ease-out"
                                loading="lazy">

                            {{-- Image Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-slate-950/5 to-transparent">
                            </div>

                            {{-- Badge --}}
                            <div class="absolute top-4 left-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full
                                         bg-white/95 backdrop-blur-sm
                                         px-3 py-1.5
                                         text-[10px] font-bold uppercase tracking-wider
                                         text-blue-700 shadow-sm">

                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>

                                    {{ $event['badge'] }}
                                </span>
                            </div>

                            {{-- Arrow --}}
                            <div
                                class="absolute bottom-4 right-4
                                    w-10 h-10 rounded-full
                                    bg-white/90 backdrop-blur-sm
                                    flex items-center justify-center
                                    text-slate-700
                                    shadow-md
                                    opacity-0 translate-y-2
                                    group-hover:opacity-100 group-hover:translate-y-0
                                    transition-all duration-300">

                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="m12 5 7 7-7 7" />
                                </svg>

                            </div>
                        </div>


                        {{-- Content --}}
                        <div class="flex flex-col flex-1 p-5 sm:p-6">

                            {{-- Title --}}
                            <h3
                                class="font-display text-xl sm:text-2xl
                                   text-slate-900
                                   leading-snug
                                   group-hover:text-blue-600
                                   transition-colors duration-300">
                                {{ $event['title'] }}
                            </h3>


                            {{-- Metadata --}}
                            <div class="mt-4 space-y-2.5">

                                {{-- Date --}}
                                <div class="flex items-start gap-2.5 text-sm text-slate-500">

                                    <div class="mt-0.5 flex-shrink-0 text-blue-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                            viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                    </div>

                                    <span class="leading-relaxed">
                                        {{ $event['date'] }}
                                    </span>

                                </div>


                                {{-- Location --}}
                                <div class="flex items-start gap-2.5 text-sm text-slate-500">

                                    <div class="mt-0.5 flex-shrink-0 text-blue-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>

                                    <span class="leading-relaxed line-clamp-2">
                                        {{ $event['location'] }}
                                    </span>

                                </div>

                            </div>


                            {{-- Bottom Action --}}
                            <div
                                class="mt-6 pt-4 border-t border-slate-100
                                    flex items-center justify-between">

                                <span
                                    class="text-sm font-semibold text-blue-600
                                       group-hover:text-blue-700
                                       transition-colors">
                                    View Event
                                </span>

                                <div
                                    class="flex items-center gap-1 text-xs font-medium
                                       text-slate-400
                                       group-hover:text-blue-500
                                       transition-colors">
                                    Learn more

                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300"
                                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M5 12h14" />
                                        <path d="m12 5 7 7-7 7" />
                                    </svg>
                                </div>

                            </div>

                        </div>

                    </a>
                @endforeach

            </div>

        </div>
    </section>
