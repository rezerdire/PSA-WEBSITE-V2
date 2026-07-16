
        @vite(['resources/css/app.css', 'resources/js/app.js'])
@section('title', 'PJA')
@extends('layouts.app')

<div x-data="{ activeTab: 'PJA' }" class="bg-white min-h-screen">

    {{-- KVP --}}
    <x-about-us-header title="Philippine Journal of Anesthesiology" description="The official journal of the Philippine Society of Anesthesiologists, Inc., publishing original research, case reports, and clinical updates in anesthesiology." />

    <x-about-us-content :panels="[
        ['key' => 'PJA','title' => '',    'image' => '/images/pja.jpg',    'alt' => 'PJA'],
  
    ]" />

</div>