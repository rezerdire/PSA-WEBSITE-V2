<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
@section('title', 'Chapter Presidents')
@extends('layouts.app')

<div x-data="{ activeTab: 'luzon' }" class="bg-white min-h-screen">

    {{-- KVP --}}
    <x-about-us-header title="Chapter Presidents" description="The presidents representing PSA chapters across the Philippines." />

    <x-sub-navbar :tabs="[
        ['key' => 'luzon',    'label' => 'Luzon'],
        ['key' => 'visayas',  'label' => 'Visayas'],
        ['key' => 'mindanao', 'label' => 'Mindanao'],
    ]" />

    <x-about-us-content :panels="[
        ['key' => 'luzon','title' => 'Luzon',    'image' => '/images/Chapter-Presidents_Luzon.png',    'alt' => 'Luzon'],
        ['key' => 'visayas','title' => 'Visayas',  'image' => '/images/Chapter-Presidents_Visayas.png',  'alt' => 'Visayas'],
        ['key' => 'mindanao','title' => 'Mindanao', 'image' => '/images/Chapter-Presidents_Mindanao.png', 'alt' => 'Mindanao'],
    ]" />

</div>