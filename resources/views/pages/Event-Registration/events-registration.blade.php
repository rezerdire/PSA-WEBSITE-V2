<?php

use Livewire\Component;

new class extends Component
{
   
};
?>

@section('title', 'Annual Convention Registration 2026')
@extends('layouts.app')
@section('content')
    <div class="container mx-auto min-h-screen px-4 pb-12 pt-24">

{{-- <x-about-us-header
    title="Annual Convention 2026"
    description="Register for the annual convention 2026!" /> --}}
{{-- <x-event-registration.event-registration-layout>
    <livewire:event-registration.event-registration-switcher />
    
</x-event-registration.event-registration-layout> --}}

<x-registration-closed />

</div>
@endsection
