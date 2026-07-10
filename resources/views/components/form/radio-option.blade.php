@props([
    'value',
    'label',
    'color'    => 'blue',   
    'active'   => false,    // if selected
    'disabled' => false,    // true for membership type
    'model'    => null,     // for alpine reactive responses
])

@php
// for discount radio button color if the option is being selected
    $palette = $color === 'red'
        ? ['active' => 'border-red-200 bg-red-50',  'text' => 'font-semibold text-red-900',  'accent' => 'accent-red-700']
        : ['active' => 'border-blue-200 bg-blue-50', 'text' => 'font-semibold text-blue-900', 'accent' => 'accent-blue-800'];
// membership type 
    $inactive = $disabled
        ? 'border-gray-100 bg-gray-50 cursor-not-allowed'
        : 'border-gray-100 hover:border-gray-300 cursor-pointer';
@endphp

{{-- changing palette color and dynamic values --}}
@if ($model)
    <label
        class="flex items-center gap-3 px-4 py-3 rounded-lg border transition cursor-pointer"
        :class="{{ $model }} === '{{ $value }}' ? '{{ $palette['active'] }}' : 'border-gray-100 hover:border-gray-300'">

        <input type="radio" value="{{ $value }}" x-model="{{ $model }}" class="{{ $palette['accent'] }}">
        <span class="text-sm" :class="{{ $model }} === '{{ $value }}' ? '{{ $palette['text'] }}' : 'text-gray-600'">
            {{ $label }}
        </span>
    </label>
@else

{{-- if active it will check the radio buttton if not it will prevent from clicking --}}
<label class="flex items-center gap-3 px-4 py-3 rounded-lg border {{ $active ? $palette['active'] : $inactive }}">
        <input type="radio" @if ($disabled) disabled @endif @if ($active) checked @endif class="{{ $palette['accent'] }}">
        <span class="text-sm {{ $active ? $palette['text'] : 'text-gray-400' }}">{{ $label }}</span>
    </label>
@endif