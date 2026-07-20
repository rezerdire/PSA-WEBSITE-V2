<?php

use Livewire\Component;

new class extends Component
{
   
};
?>

@section('title', 'Annual Convention Registration 2026')
@extends('layouts.app')
@section('content')

<x-about-us-header
    title="Annual Convention 2026"
    description="Register for the annual convention 2026!" />
<x-event-registration.event-registration-layout>
{{-- Main Content --}}
<livewire:event-registration.psa-checker />
            {{-- Registration Form Card --}}
            <x-event-registration.form-layout>
                <livewire:event-registration.event-reg-form />
            </x-event-registration.event-registration-layout>

</x-event-registration.event-registration-layout>
@endsection 