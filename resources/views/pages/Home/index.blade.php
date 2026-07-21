<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
@section('title', 'Philippine Society of Anesthesiologists')
@extends('layouts.app')
        @vite(['resources/css/app.css', 'resources/js/app.js'])

@section('content')
    <x-hero-section />
    <x-mission-vision-section />
    <x-convention-highlight />
    <x-recent-events />

@livewire('gallery-section')
    <x-contact-section />
@endsection

