<?php

use App\Models\GalleryDay;
use App\Models\GalleryEvent;
use Livewire\Component;

new class extends Component
{
    public GalleryEvent $event;
    public GalleryDay $day;

    public function mount(GalleryEvent $event, GalleryDay $day): void
    {
        $this->event = $event;
        $this->day = $day;
    }
};
?>

@extends('layouts.app')

@section('title', 'Philippine Society of Anesthesiologists')

@section('content')
    <livewire:gallery.day-page :event="$event" :day="$day" />
@endsection