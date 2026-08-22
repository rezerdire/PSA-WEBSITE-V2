@section('title', 'MH National Registry')
@extends('layouts.app')
@section('content')



    <x-about-us-header title="MH-Registry" description="" />

    {{-- Registration Form Card --}}
    <livewire:mh-registry.mh-registry-form />
    {{-- <x-mh-registry.mh-registry-form /> --}}

@endsection