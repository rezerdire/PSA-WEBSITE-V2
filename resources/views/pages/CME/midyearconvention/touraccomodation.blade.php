@extends('layouts.app')

@section('title', 'Midyear Convention 2026')

@section('content')

<div x-data="{ activeTab: 'tour-accomodation' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Midyear Convention 2026" description="May 14, 2026 | KCC Events & Convention Center | General Santos City" />

     

 <x-about-us-content :panels="[
    [
        'key' => 'tour-accomodation',
        'image' => '/midyearconvention/tour and transpo.jpg',
        'alt' => 'Day 1',
    ],
    [
        'key' => 'tour-accomodation',
        'image' => '/midyearconvention/hotel and accom.jpg',
        'alt' => 'Day 2',
    ],

]" />
</div>

@endsection