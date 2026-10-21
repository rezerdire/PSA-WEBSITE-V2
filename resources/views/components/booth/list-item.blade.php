@props(['item'])

@php($visitedAt = $item['visited_at'] ?? null)
@php($visited = filled($visitedAt))

<li wire:key="booth-{{ $item['id'] }}"
    class="flex items-center gap-3 rounded-xl border p-3 transition-colors
           {{ $visited ? 'border-emerald-100 bg-emerald-50/50' : 'border-slate-200 bg-white' }}">
    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                {{ $visited ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
        @if ($visited)
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <rect x="4" y="4" width="16" height="16" rx="3" />
            </svg>
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <p class="break-words text-sm font-bold {{ $visited ? 'text-emerald-900' : 'text-slate-800' }}">
            {{ $item['name'] }}
        </p>
        <p class="mt-0.5 break-words text-xs text-slate-500">
            {{ $item['code'] }}
            @if ($item['location'])
                <span class="px-0.5">·</span>{{ $item['location'] }}
            @endif
        </p>
    </div>

    @if ($visited)
        <span class="max-w-[7rem] shrink-0 text-right text-[11px] font-bold text-emerald-700">
            Visited
            @if ($visitedAt)
                <span class="block font-medium text-emerald-600">{{ $visitedAt->format('M j, g:i A') }}</span>
            @endif
        </span>
    @else
        <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
            Pending
        </span>
    @endif
</li>
