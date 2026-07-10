<?php
use Livewire\Component;
new class extends Component {};
?>

@section('title', 'Officers & Boards - PSA')
@extends('layouts.app')
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Officers & Boards --}}
<div x-data="{ activeTab: 'executive' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Officers & Boards"
        description="Meet the dedicated leaders who guide and govern the PSA." />

    <x-sub-navbar :tabs="[
        ['key' => 'executive',  'label' => 'Executive Officers'],
        ['key' => 'directors',  'label' => 'Board of Directors'],
        ['key' => 'regional',   'label' => 'Regional Directors'],
    ]" />

    <x-about-us-content :panels="[
        ['key' => 'executive',  'title' => 'Executive Officers', 'image' => '/images/PSA_Executive_Officers.png',  'alt' => 'PSA Executive Officers'],
        ['key' => 'directors',  'title' => 'Board of Directors', 'image' => '/images/PSA_Board_of_Directors.png',  'alt' => 'PSA Board of Directors'],
        ['key' => 'regional',   'title' => 'Regional Directors', 'image' => '/images/PSA_Regional_Directors.png',   'alt' => 'PSA Regional Directors'],
    ]" />
</div>