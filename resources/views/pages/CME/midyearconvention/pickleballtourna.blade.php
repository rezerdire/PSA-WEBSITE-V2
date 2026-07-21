@extends('layouts.app')

@section('title', 'Midyear Convention 2026')

@section('content')

<div x-data="{ activeTab: 'pickleball' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Midyear Convention 2026" description="May 14, 2026 | KCC Events & Convention Center | General Santos City" />

     

 <x-about-us-content :panels="[
    [
        'key' => 'pickleball',
        'image' => '/midyearconvention/pickleball_tournament.jpg',
        'alt' => 'Day 1',
    ],
    [
        'key' => 'pickleball',
        'image' => '/midyearconvention/pickleball_tournament2.jpg',
        'alt' => 'Day 2',
    ],
  
]" />
</div>

