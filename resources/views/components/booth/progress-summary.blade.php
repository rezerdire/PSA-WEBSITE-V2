@props([
    'total' => 0,
    'visited' => 0,
    'percent' => 0,
])

@php($pending = max(0, $total - $visited))

<div class="border-t border-slate-100 px-5 py-6 sm:px-8">
    <div class="flex items-end justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Your booth journey</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $percent }}% completed</p>
        </div>

        <p class="text-2xl font-extrabold tabular-nums text-slate-900">
            {{ $visited }}<span class="text-base font-semibold text-slate-400"> / {{ $total }}</span>
        </p>
    </div>

    <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100">
        <div class="h-full rounded-full bg-blue-600 transition-all duration-700" style="width: {{ $percent }}%"
            role="progressbar" aria-label="Booth visit progress" aria-valuemin="0" aria-valuemax="100"
            aria-valuenow="{{ $percent }}"></div>
    </div>

    <div class="mt-5 grid grid-cols-2 gap-3">
        <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-emerald-800">Visited</span>
            </div>
            <p class="mt-2 text-2xl font-extrabold text-emerald-800">{{ $visited }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-200 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 7v5l3 2" />
                    </svg>
                </span>
                <span class="text-xs font-semibold text-slate-600">Pending</span>
            </div>
            <p class="mt-2 text-2xl font-extrabold text-slate-800">{{ $pending }}</p>
        </div>
    </div>

    {{ $slot }}
</div>
