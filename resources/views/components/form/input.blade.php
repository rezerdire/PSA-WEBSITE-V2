@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'type' => 'text',
    'readonly' => false,
    'pattern' => null,          
    'patternMessage' => 'Invalid format.',
])

<div @if ($pattern) x-data="{ value: @entangle($name) }" @endif>
    @if ($label)
        <label class="block text-xs font-medium text-gray-500 mb-1.5">
            {{ $label }}
            @if ($hint)
                <span class="text-gray-400">{{ $hint }}</span>
            @endif
        </label>
    @endif

    <input type="{{ $type }}"
        @if ($readonly) readonly @endif
        @if ($pattern)
            x-model="value"
            x-bind:class="{ '!border-red-400 focus:!ring-red-400' : value.length > 0 && !/{{ $pattern }}/.test(value) }"
        @endif
        {{ ($pattern ? $attributes->whereDoesntStartWith('wire:model') : $attributes)->class([
            'w-full border rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]',
            'border-gray-100 bg-gray-50 text-gray-400 cursor-not-allowed' => $readonly,
            'border-gray-200' => ! $readonly,
        ]) }}>

    @if ($pattern)
        <p x-show="value.length > 0 && !/{{ $pattern }}/.test(value)" x-cloak
           class="text-xs text-red-500 mt-1">
            {{ $patternMessage }}
        </p>
    @endif

    @if ($name)
        @error($name)
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    @endif
</div>