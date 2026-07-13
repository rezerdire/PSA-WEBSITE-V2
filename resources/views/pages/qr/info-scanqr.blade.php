<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

@extends('layouts.app')

@section('title', 'Member Scanner - PSA')

@vite(['resources/css/app.css'])

@section('content')
    <livewire:qr.info-scan-qr />
@endsection