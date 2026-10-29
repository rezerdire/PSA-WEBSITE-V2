@extends('layouts.app')

@section('title', 'Booth Check-in')
@section('content')
<x-booth.page-shell>
    <livewire:booth.booth-scan :booth_code="$booth_code" />
</x-booth.page-shell>
@endsection