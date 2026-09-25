<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
@section('title', 'Login')
@extends('layouts.app')
@section('content')


<livewire:account.login />  
@endsection