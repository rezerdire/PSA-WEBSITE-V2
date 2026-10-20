@extends('layouts.app')

@section('title', 'Booth Check-in')
@section('content')
<div class="flex items-center min-h-dvh mt-10">
    <livewire:booth.booth-scan :booth_code="$booth_code" />
</div>
    @endsection