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
@section('content')
{{-- default tab --}}
    <x-about-us-header title="PSA Hymn" description="The official hymn of the Philippine Society of Anesthesiologists, Inc." />

    <x-about-us-content :panels="[
       
        ['key' => 'psa-hymn','youtube' => 'DLeUtxeIp9w'],
    ]" />

@endsection