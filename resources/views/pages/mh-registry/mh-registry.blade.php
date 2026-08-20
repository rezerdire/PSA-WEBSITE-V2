<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

@section('title', 'MH National Registry')
@extends('layouts.app')
@section('content')



    <x-about-us-header title="MH-Registry" description="" />
    <x-event-registration.event-registration-layout>
        {{-- Registration Form Card --}}
        <livewire:mh-registry />
    </x-event-registration.event-registration-layout>

@endsection