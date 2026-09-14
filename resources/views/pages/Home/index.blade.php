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
   <livewire:video-section videoUrl="video/Elimination Round Winners.mp4" title="TNT ELIMINATION ROUND WINNERS" :forcePortrait="true"/>


   <livewire:video-section videoUrl="video/simwarsvideo.mp4" title="Sim Wars" />
    <x-mission-vision-section />
    <x-convention-highlight />
    <x-recent-events />

@livewire('gallery-section')
    <x-contact-section />
@endsection

