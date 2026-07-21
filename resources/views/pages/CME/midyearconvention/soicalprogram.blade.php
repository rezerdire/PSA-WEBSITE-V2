@extends('layouts.app')

@section('title', 'Midyear Convention 2026')

@section('content')

<div x-data="{ activeTab: 'socialprogram' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Midyear Convention 2026" description="May 14, 2026 | KCC Events & Convention Center | General Santos City" />

     

 <x-about-us-content :panels="[
    [
        'key' => 'socialprogram',
        'image' => '/midyearconvention/Opening Ceremony.png',
        'alt' => 'Day 1',
    ],
    [
        'key' => 'socialprogram',
        'image' => '/midyearconvention/Fellowship Night.png',
        'alt' => 'Day 2',
    ],
    [
        'key' => 'socialprogram',
        'image' => '/midyearconvention/Closing Ceremony.png',
        'alt' => 'Day 3',
    ],
]" />
</div>

