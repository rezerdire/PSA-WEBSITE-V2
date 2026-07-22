@extends('layouts.app')

@section('title', 'Midyear Convention 2026')

@section('content')

<div x-data="{ activeTab: 'poster' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Scientific Program" description="May 14, 2026 | KCC Events & Convention Center | General Santos City" />

 <x-about-us-content :panels="[
    [
        'key' => 'poster',
        'image' => '/midyearconvention/day1.png',
        'alt' => 'Day 1',
    ],
    [
        'key' => 'poster',
        'image' => '/midyearconvention/day2.png',
        'alt' => 'Day 2',
    ],
    [
        'key' => 'poster',
        'image' => '/midyearconvention/day3.png',
        'alt' => 'Day 3',
    ]
]" />
</div>
@endsection