@section('title', 'MH National Registry')
@extends('layouts.app')
@section('content')



    {{-- <x-about-us-header title="MH-Registry" description="" /> --}}
    <div class = "mt-10">
    {{-- Registration Form Card --}}
    <livewire:mh-registry.mh-registry-form />
    {{-- <x-mh-registry.mh-registry-form /> --}}
    </div>
@endsection