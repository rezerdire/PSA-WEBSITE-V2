<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
@section('title', 'Philippine Society of Anesthesiologists')
@extends('layouts.app')

@section('content')
    <x-hero-section />
    <x-mission-vision-section />
    <x-recent-events />

@livewire('gallery-section')
    <x-contact-section />
@endsection

