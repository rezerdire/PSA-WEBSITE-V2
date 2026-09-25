<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
@section('title', 'Activate Account')
@extends('layouts.app')
@section('content')

 <livewire:account.activate />

@endsection 