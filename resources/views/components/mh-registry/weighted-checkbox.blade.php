@props(['model', 'points'])
<label class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm">
    <input type="checkbox" wire:model.live="{{ $model }}" class="mt-0.5 h-4 w-4 rounded text-[#000066]">
    <span>{{ $slot }} <b>({{ $points }})</b></span>
</label>