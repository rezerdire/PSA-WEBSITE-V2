<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

@section('title', 'Legacy')
@extends('layouts.app')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<div x-data="{ activeTab: 'past-presidents' }" class="bg-white min-h-screen">
{{-- default tab --}}
    <x-about-us-header title="Legacy" description="Honoring the distinguished leaders who have shaped the PSA." />

    <x-sub-navbar :tabs="[
        ['key' => 'past-presidents',  'label' => 'Past Presidents'],
        ['key' => 'quintin-awardee',  'label' => 'Quintin J. Gomez Awardee'],
        ['key' => 'silao-awardee',    'label' => 'Manuel V. Silao Awardee'],
        ['key' => 'psa-hymn',         'label' => 'PSA Hymn'],
    ]" />

    <x-about-us-content :panels="[
        ['key' => 'past-presidents','image' => '/images/Past_Presidents_2025.png',   'alt' => 'PSA Past Presidents'],
        ['key' => 'quintin-awardee', 'image' => '/images/Quintin_J_Gomez_Awardees.png',   'alt' => 'Quintin J. Gomez Awardee'],
        ['key' => 'silao-awardee',   'image' => '/images/Manuel_Silao_Leadership_Awardee.png','alt' => 'Manuel V. Silao Awardee'],
        ['key' => 'psa-hymn',        'youtube' => 'hkIcSJ5enp8'],
    ]" />

</div>