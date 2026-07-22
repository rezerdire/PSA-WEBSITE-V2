@extends('layouts.app')

@section('title', 'Midyear Convention 2026')

@section('content')

<div x-data="{ activeTab: 'poster' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Midyear Convention 2026" description="May 14, 2026 | KCC Events & Convention Center | General Santos City" />

        
        <div class = "flex justify-center">
    <x-sub-navbar :tabs="[
        ['key' => 'poster',  'label' => 'Poster'],
        ['key' => 'rates',  'label' => 'Registration Rates'],

    ]" />
</div>
    <x-about-us-content :panels="[
        ['key' => 'poster',   'image' => '/midyearconvention/PSA_MIDYEAR_2026_POSTER.jpg',  'alt' => 'POSTER'],
        ['key' => 'rates',   'image' => '/midyearconvention/PSA MIDYEAR 2026 RATES.png',  'alt' => 'RATES'],

    ]" />
</div>
@endsection
