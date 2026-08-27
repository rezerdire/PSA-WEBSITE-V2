<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

@section('title', 'Legacy')
@extends('layouts.app')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@section('content')
    <div x-data="{ activeTab: 'past-presidents' }" class="bg-white min-h-screen">
        {{-- default tab --}}
        <x-about-us-header title="Legacy" description="Honoring the distinguished leaders who have shaped the PSA." />

        <x-sub-navbar :tabs="[
            ['key' => 'past-presidents', 'label' => 'Past Presidents'],
            ['key' => 'quintin-awardee', 'label' => 'Quintin J. Gomez Awardee'],
            ['key' => 'silao-awardee', 'label' => 'Manuel V. Silao Awardee'],
            ['key' => 'outstanding-chapters', 'label' => 'Most Outstanding Chapters'],
            ['key' => 'psa-hymn', 'label' => 'PSA Hymn'],
        ]" />

        <x-about-us-content :panels="[
            ['key' => 'past-presidents', 'image' => '/images/Past_Presidents_2025.png', 'alt' => 'PSA Past Presidents'],
            [
                'key' => 'quintin-awardee',
                'image' => '/images/Quintin_J_Gomez_Awardees.png',
                'alt' => 'Quintin J. Gomez Awardee',
            ],
            [
                'key' => 'silao-awardee',
                'image' => '/images/Manuel_Silao_Leadership_Awardee.png',
                'alt' => 'Manuel V. Silao Awardee',
            ],
            [
                'key' => 'outstanding-chapters',
                'title' => 'List of Most Outstanding Chapters',
                'list' => [
                    ['year' => 1993, 'chapter' => 'CENTRAL LUZON'],
                    ['year' => 1994, 'chapter' => 'SOUTHERN TAGALOG'],
                    ['year' => 1995, 'chapter' => 'WESTERN MINDANAO'],
                    ['year' => 1996, 'chapter' => 'BICOL'],
                    ['year' => 1997, 'chapter' => 'SOUTHERN MINDANAO'],
                    ['year' => 1998, 'chapter' => 'BICOL'],
                    ['year' => 1999, 'chapter' => 'EASTERN VISAYAS'],
                    ['year' => 2000, 'chapter' => 'ILOILO'],
                    ['year' => 2001, 'chapter' => 'NO NOMINEES'],
                    ['year' => 2002, 'chapter' => 'CEBU CENTRAL VISAYAS'],
                    ['year' => 2003, 'chapter' => 'NEGROS OCCIDENTAL'],
                    ['year' => 2004, 'chapter' => 'CEBU CENTRAL VISAYAS'],
                    ['year' => 2005, 'chapter' => 'NORTHERN MINDANAO'],
                    ['year' => 2006, 'chapter' => 'NEGROS OCCIDENTAL'],
                    ['year' => 2007, 'chapter' => 'ILOILO-PANAY'],
                    ['year' => 2008, 'chapter' => 'SOUTHERN TAGALOG'],
                    ['year' => 2009, 'chapter' => 'SOUTHERN TAGALOG'],
                    ['year' => 2010, 'chapter' => 'ILOILO-PANAY'],
                    ['year' => 2011, 'chapter' => 'ILOILO-PANAY'],
                    ['year' => 2012, 'chapter' => 'NEGROS OCCIDENTAL'],
                    ['year' => 2013, 'chapter' => 'SOUTHERN MINDANAO'],
                    ['year' => 2014, 'chapter' => 'NORTHERN MINDANAO'],
                    ['year' => 2015, 'chapter' => 'BAGUIO-BENGUET-MT. PROVINCE'],
                    ['year' => 2016, 'chapter' => 'ILOILO PANAY'],
                    ['year' => 2017, 'chapter' => 'NO NOMINEES'],
                    ['year' => 2018, 'chapter' => 'SOCSKSARGEN'],
                    ['year' => 2019, 'chapter' => 'BAGUIO-BENGUET-MT. PROVINCE'],
                    ['year' => 2020, 'chapter' => 'NO NOMINEES'],
                    ['year' => 2021, 'chapter' => 'CEBU CENTRAL VISAYAS'],
                    ['year' => 2022, 'chapter' => 'NO NOMINEES'],
                    ['year' => 2023, 'chapter' => 'CEBU CENTRAL VISAYAS'],
                    ['year' => 2024, 'chapter' => 'SOUTHERN TAGALOG'],
                    ['year' => 2025, 'chapter' => 'SOUTHERN TAGALOG'],
                ],
            ],
            ['key' => 'psa-hymn', 'youtube' => 'DLeUtxeIp9w'],
        ]" />



    </div>
@endsection
