<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
@section('title', 'Officers & Boards - PSA')
@extends('layouts.app')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@section('content')
{{-- subspecialty and SIG --}}
<div x-data="{ activeTab: 'subspecialty' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Subspecialty & SIG"
        description="Explore the specialized groups within the PSA." />

    <x-sub-navbar :tabs="[
        ['key' => 'subspecialty', 'label' => 'Subspecialty'],
        ['key' => 'sig',          'label' => 'Special Interest Groups'],
        ['key' => 'rasphil',      'label' => 'RASPHIL Convention 2026'],
    ]" />

    <x-about-us-content :panels="[
        ['key' => 'subspecialty',  'image' => '/images/Subspecialty-and-ISG.png', 'alt' => 'PSA Subspecialty'],
        ['key' => 'sig',           'image' => '/images/Special-Interest-Group.png',      'alt' => 'PSA Special Interest Groups'],
        ['key' => 'rasphil',      'title' => 'RASPHIL Convention 2026', 'youtube' => 'MD38OAWUjBs',   'alt' => 'RASPHIL Convention 2026', 'subtitle' => '23rd - 24th January 2026 | Metrocentre, Tagbilaran City, Bohol'],
   ]" />

</div>
@endsection