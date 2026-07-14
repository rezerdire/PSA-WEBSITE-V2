@extends('layouts.app')

@section('title', 'Gallery - Philippine Society of Anesthesiologists')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-12">

    {{-- Page header --}}
    <div class="mb-10">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1.5">PSA</p>
        <div class="flex items-center gap-3">
            <span class="w-1 h-8 bg-blue-600 rounded-full"></span>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Photo Gallery</h1>
        </div>
        <p class="mt-2 text-slate-500 text-sm ml-4">Browse photos from past PSA events and conventions.</p>
    </div>

    {{-- Event cards --}}
    <div class="space-y-10">
        @foreach ($events as $event)
            @php $previews = $event->previewImages; @endphp

            <div
                x-data="{
                    current: 0,
                    total: {{ $previews->count() }},
                    timer: null,
                    init() {
                        if (this.total > 1) this.timer = setInterval(() => this.next(), 4500);
                    },
                    destroy() { clearInterval(this.timer); },
                    next() { this.current = (this.current + 1) % this.total; },
                    prev() { this.current = (this.current - 1 + this.total) % this.total; },
                    pause() { clearInterval(this.timer); },
                    resume() { if (this.total > 1) this.timer = setInterval(() => this.next(), 4500); },
                }"
                @mouseenter="pause()"
                @mouseleave="resume()"
                class="rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-white"
            >
                {{-- Carousel --}}
                <div class="relative w-full overflow-hidden" style="aspect-ratio: 16/7;">

                    {{-- Slides --}}
                    @foreach ($previews as $i => $image)
                        <div
                            class="absolute inset-0 transition-opacity duration-700"
                            :class="{{ $i }} === current ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                        >
                            <img
                                src="{{ asset($image->large_path) }}"
                                class="w-full h-full object-cover"
                                alt="{{ $event->name }}"
                                loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                            >
                        </div>
                    @endforeach

                    {{-- Gradient overlay --}}
                    <div class="absolute inset-0 z-20 bg-gradient-to-t from-black/70 via-black/20 to-transparent pointer-events-none"></div>

                    {{-- Event title overlay --}}
                    <div class="absolute bottom-0 left-0 right-0 z-30 px-6 pb-5 pointer-events-none">
                        <h2 class="text-white text-2xl sm:text-3xl font-bold tracking-tight drop-shadow">
                            {{ $event->name }}
                        </h2>
                        <p class="text-white/70 text-sm mt-0.5">
                            Click a day to browse more photos
                        </p>
                    </div>

                    {{-- Prev / Next arrows --}}
                    @if ($previews->count() > 1)
                        <button
                            @click.stop="prev()"
                            class="absolute left-3 top-1/2 -translate-y-1/2 z-30 w-9 h-9 flex items-center justify-center rounded-full bg-black/30 hover:bg-black/50 text-white transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button
                            @click.stop="next()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 z-30 w-9 h-9 flex items-center justify-center rounded-full bg-black/30 hover:bg-black/50 text-white transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @endif

                    {{-- Dot indicators --}}
                    @if ($previews->count() > 1)
                        <div class="absolute bottom-2 right-5 z-30 flex items-center gap-1.5 ">
                            @foreach ($previews as $i => $image)
                                <button
                                    @click.stop="current = {{ $i }}"
                                    class="rounded-full transition-all duration-300"
                                    :class="{{ $i }} === current ? 'w-5 h-2 bg-white' : 'w-2 h-2 bg-white/50 hover:bg-white/80'"
                                ></button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Day pills --}}
                <div class="px-5 py-4 flex flex-wrap gap-2 bg-white">
                    @foreach ($event->days as $day)
                        <a
                            href="{{ route('gallery.day', [$event, $day]) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium border border-slate-200 text-slate-600hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9h18M9 21V9m6 12V9M4 3h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1z"/>
                            </svg>
                            {{ $day->label }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection