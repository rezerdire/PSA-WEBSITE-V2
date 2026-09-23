@props(['number', 'title', 'description', 'active' => false])

<div
    class="flex items-start gap-3 {{ $active ? 'rounded-xl border border-blue-100 bg-blue-50/70 p-4' : 'rounded-xl border border-slate-200 bg-slate-50 p-4' }}">
    <div
        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $active ? 'bg-blue-700 text-white' : 'bg-slate-200 text-slate-600' }}">
        {{ $number }}
    </div>

    <div>
        <p class="text-sm font-semibold text-slate-800">{{ $title }}</p>
        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $description }}</p>
    </div>
</div>
