@props(['icon' => 'search', 'title', 'dashed' => false])

<div
    class="rounded-2xl border {{ $dashed ? 'border-dashed' : '' }} border-slate-200 bg-slate-50 px-5 py-10 text-center">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200">
        <x-account.activate-component.icon :name="$icon" class="h-5 w-5 text-slate-400" strokeWidth="1.8" />
    </div>

    <p class="mt-4 text-sm font-medium text-slate-600">{{ $title }}</p>

    <p class="mt-1 text-xs leading-5 text-slate-400">
        {{ $slot }}
    </p>
</div>
