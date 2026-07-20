<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

@section('title', 'PSA Hymn')
@extends('layouts.app')
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- default tab --}}
    <x-about-us-header title="PSA Hymn" description="The official hymn of the Philippine Society of Anesthesiologists, Inc." />

    <x-about-us-content :panels="[
       
        ['key' => 'psa-hymn',        'title' => 'PSA Hymn','youtube' => 'DLeUtxeIp9w'],
    ]" />

