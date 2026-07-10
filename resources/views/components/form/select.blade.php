@props(['label' => null, 'name' => null])

<div>
    @if ($label)
        <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $label }}</label>
    @endif

    <select
        {{ $attributes->class([
            'w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]',
        ]) }}
    >
        {{ $slot }}
    </select>

    @if ($name)
        @error($name)
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    @endif
</div>

