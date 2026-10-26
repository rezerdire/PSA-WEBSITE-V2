
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
                'key' => 'registration',
                'title' => 'Registration Form',
                'description' => 'Register for the annual convention',
                'icon' => 'document-text',
                'href' => route('Event-Registration'),
            ],
            [
                'key' => 'workshops',
                'title' => 'Workshops Registration',
                'description' => 'Register for the convention workshops',
                'icon' => 'clipboard-document-check',
                'href' => route('workshop'),
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
                'description' => 'Explore evening events and networking activities',
                'icon' => 'sparkles',
                'href' => '#',
            ],
            [
                'key' => 'booth-checker',
                'title' => 'Booth Checker',
                'description' => 'Check the booths you have visited',
                'icon' => 'clipboard-document-list',
                'href' => route('booth.checker'),
            ],
          
        ];
    @endphp

    <div class="min-h-screen bg-white">
        {{-- Header --}}
        <x-about-us-header
            title="ANNUAL CONVENTION 2026"
            description="November 25-27, 2026 | Marriott Grand Ballroom | Pasay City"
        />

        {{-- Convention Links --}}
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-16 sm:pb-24">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach ($links as $link)
                    <a
                        href="{{ $link['href'] }}"
                        @if ($link['href'] === '#')
                            aria-disabled="true"
                        @endif
                        class="group relative flex flex-col items-center text-center
                               rounded-xl border border-slate-200 bg-white
                               p-6 sm:p-8 shadow-md shadow-slate-200/60
                               transition-all duration-300
                               hover:-translate-y-1 hover:border-blue-200
                               hover:shadow-xl
                               focus:outline-none focus:ring-2
                               focus:ring-blue-600 focus:ring-offset-2"
                    >
                        {{-- Icon --}}
                        <div
                            class="mb-5 flex h-14 w-14 sm:h-16 sm:w-16
                                   items-center justify-center rounded-xl
                                   bg-blue-50 transition-colors duration-300
                                   group-hover:bg-blue-600"
                        >
                            <x-dynamic-component
                                :component="'heroicon-o-' . $link['icon']"
                                class="h-7 w-7 sm:h-8 sm:w-8 text-[#000066]
                                       transition-colors duration-300
                                       group-hover:text-white"
                            />
                        </div>

                        {{-- Title --}}
                        <h3 class="mb-2 font-display text-lg sm:text-xl font-semibold text-[#000066]">
                            {{ $link['title'] }}
                        </h3>

                        {{-- Description --}}
                        <p class="text-sm leading-relaxed text-slate-500">
                            {{ $link['description'] }}
                        </p>

                        {{-- Bottom Hover Indicator --}}
                        <span
                            class="absolute bottom-0 left-0 right-0 h-1
                                   origin-left scale-x-0 rounded-b-xl
                                   bg-blue-600 transition-transform duration-300
                                   group-hover:scale-x-100"
                        ></span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
