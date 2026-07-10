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

{{-- subspecialty and SIG --}}
<div x-data="{ activeTab: 'subspecialty' }" class="bg-white min-h-screen">

    <x-about-us-header
        title="Subspecialty & SIG"
        description="Explore the specialized groups within the PSA." />

    <x-sub-navbar :tabs="[
        ['key' => 'subspecialty', 'label' => 'Subspecialty'],
        ['key' => 'sig',          'label' => 'Special Interest Groups'],
    ]" />

    <x-about-us-content :panels="[
        ['key' => 'subspecialty', 'title' => 'Subspecialty', 'image' => '/images/Subspecialty-and-ISG.png', 'alt' => 'PSA Subspecialty'],
        ['key' => 'sig',          'title' => 'Special Interest Groups', 'image' => '/images/Special-Interest-Group.png',          'alt' => 'PSA Special Interest Groups'],
    ]" />

</div>