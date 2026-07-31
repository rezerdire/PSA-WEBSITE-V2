
        @vite(['resources/css/app.css', 'resources/js/app.js'])
@section('title', 'Interesting Case')
@extends('layouts.app')
@section('content')

<div x-data="{ activeTab: 'PJA' }" class="bg-white min-h-screen">

    {{-- KVP --}}
 <x-about-us-header
    title="Interesting Case"
    description="Share your unique clinical cases, engage with colleagues, and inspire learning through knowledge, experience, and innovation."
/>

    <x-about-us-content :panels="[
        ['key' => 'PJA','title' => '',    'image' => '/images/InterestingCase.png',    'alt' => 'Interesting Case'],
  
    ]" />

</div>
@endsection