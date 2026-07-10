@props(['label', 'value', 'mono' => false])

<div>
    <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $label }}</label>
    <input
        type="text"
        value="{{ $value }}"
        readonly
        class="w-full border border-gray-100 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-400 cursor-not-allowed {{ $mono ? 'font-mono' : '' }}">
</div>

{{-- dynamic value for label --}}