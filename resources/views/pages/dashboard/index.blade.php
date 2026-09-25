<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@section('title', 'Dashboard')
@extends('layouts.app-bare')
@section('content')

<livewire:account.user.dashboard />

@endsection