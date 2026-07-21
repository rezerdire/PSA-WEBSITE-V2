@vite(['resources/css/app.css', 'resources/js/app.js'])

@extends('layouts.app')

@section('title', 'Midyear Convention 2026')

@section('content')

<div x-data="{ activeTab: 'poster-rates' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Annual Convention 2026" description="November 25-27, 2026 | Mariott Grand Ballroom | Pasay City" />


    <x-about-us-content :panels="[
        ['key' => 'poster-rates',   'image' => '/annual-convention/58th Annual Convention Rates.png',  'alt' => 'POSTER&RATES'],

    ]" />
</div>