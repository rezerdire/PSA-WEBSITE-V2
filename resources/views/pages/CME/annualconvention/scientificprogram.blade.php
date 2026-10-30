@extends('layouts.app')

@section('title', 'Annual Convention 2026 – Scientific Program')

@section('content')
    <div x-data="{ activeTab: 'scientific-program' }" class="min-h-screen bg-white">
        <x-about-us-header
            title="Annual Convention 2026"
            description="November 25–27, 2026 | Marriott Grand Ballroom | Pasay City"
        />

        <div class="flex justify-center mt-2">
            <a
                href="{{ route('Event-Registration') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-200 transition-all hover:-translate-y-0.5 hover:bg-blue-700 sm:px-6 sm:py-3 sm:text-base"
            >
                Register for the Convention
            </a>
        </div>

        <x-about-us-content :panels="[
            [
                'key' => 'scientific-program',
                'image' => '/annual-convention/ScientificProgram1.png',
                'alt' => 'Annual Convention 2026 scientific program poster with workshops, forums, and convention information',
                'zoomable' => true,
            ],
            [
                'key' => 'scientific-program',
                'image' => '/annual-convention/ScientificProgram2.png',
                'alt' => 'Annual Convention 2026 scientific program schedule for Days 1 through 3',
                'zoomable' => true,
            ],
        ]" />
    </div>
@endsection
