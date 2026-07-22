@extends('layouts.app')

@section('title', 'Midyear Convention 2026')

@section('content')

<div x-data="{ activeTab: 'poster' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Midyear Convention 2026" description="Convention Poster" />

        <div class = "flex justify-center">
    <x-sub-navbar :tabs="[
        ['key' => 'poster',  'label' => 'Poster'],
        ['key' => 'rates',  'label' => 'Registration Rates'],

    ]" />
</div>
    <x-about-us-content :panels="[
        ['key' => 'poster',   'image' => '/midyearconvention/PSA_MIDYEAR_2026_POSTER.jpg',  'alt' => 'PSA MID YEAR 2026 POSTER'],
        ['key' => 'rates',   'image' => '/midyearconvention/PSA MIDYEAR 2026 RATES.png',  'alt' => 'PSA Board of Directors'],

    ]" />
</div>
@endsection