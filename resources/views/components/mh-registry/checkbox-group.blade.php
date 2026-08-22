@props(['name', 'options' => [], 'cols' => 3])
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-{{ $cols }}">
    @foreach($options as $option)
        <label
            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50/50">
            <input type="checkbox" value="{{ $option }}" wire:model="{{ $name }}"
                class="h-4 w-4 rounded border-slate-300 text-[#000066] focus:ring-[#000066]">
            {{ $option }}
        </label>
    @endforeach
</div>