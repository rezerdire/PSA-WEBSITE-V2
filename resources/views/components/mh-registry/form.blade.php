@props(['for' => null, 'required' => false])
<label for="{{ $for }}" class="mb-1.5 block text-xs font-bold text-slate-600">
    {{ $slot }}
    @if($required)<span class="text-red-500">*</span>@endif
</label>