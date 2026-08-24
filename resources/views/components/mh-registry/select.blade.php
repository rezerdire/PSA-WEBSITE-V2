@props(['label' => null, 'name', 'options' => [], 'required' => false, 'placeholder' => 'Select'])
<div>
    @if($label)
        <x-form.label :for="$name" :required="$required">{{ $label }}</x-form.label>
    @endif
    <select wire:model="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50']) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>