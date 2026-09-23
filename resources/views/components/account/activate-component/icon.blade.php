@props(['name', 'class' => 'h-5 w-5'])

@php
    $paths = [
        'back' => 'M10 19l-7-7m0 0l7-7m-7 7h18',
        'search' => 'M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z',
        'spinner' => null, // handled separately below
        'check' => 'M5 13l4 4L19 7',
        'arrow-right' => 'M9 5l7 7-7 7',
        'arrow-left' => 'M15 19l-7-7 7-7',
        'warning' =>
            'M12 9v4m0 4h.01M10.29 3.86l-8.82 15a2 2 0 001.71 3h17.64a2 2 0 001.71-3l-8.82-15a2 2 0 00-3.42 0z',
        'warning-thin' =>
            'M12 9v3m0 4h.01M10.29 3.86l-8.82 15a2 2 0 001.71 3h17.64a2 2 0 001.71-3l-8.82-15a2 2 0 00-3.42 0z',
        'no-results' =>
            'M9.5 9.5h.01M14.5 9.5h.01M8 14c1.1 1 2.45 1.5 4 1.5s2.9-.5 4-1.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'mail' =>
            'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'help' => 'M8.23 9.53a4 4 0 117.54 0c0 1.8-1.16 2.65-2.22 3.42-.87.63-1.55 1.13-1.55 2.55m0 3h.01',
    ];
@endphp

@if ($name === 'spinner')
    <svg {{ $attributes->merge(['class' => "$class animate-spin"]) }} fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
    </svg>
@else
    <svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $strokeWidth ?? '2' }}"
            d="{{ $paths[$name] ?? '' }}" />
    </svg>
@endif
