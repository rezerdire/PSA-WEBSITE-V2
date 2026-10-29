@props([
    'name' => null,
    'memberId' => null,
    'chapter' => null,
    'action' => null,
    'actionLabel' => null,
])

<div class="relative rounded-2xl border border-slate-200 bg-slate-50/60 p-5 sm:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
        <div class="flex min-w-0 flex-1 items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="8" r="4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 21v-2a7 7 0 0 1 14 0v2" />
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Verified member</p>

                @if ($name)
                    <p class="mt-1 break-words text-lg font-extrabold text-slate-900">{{ $name }}</p>
                @endif

                @if ($memberId)
                    <p class="mt-1 text-sm text-slate-600">
                        PSA ID: <span class="font-semibold text-slate-800">{{ $memberId }}</span>
                    </p>
                @endif

                @if ($chapter)
                    <p class="mt-1 text-sm text-slate-600">
                        Chapter: <span class="font-semibold text-slate-800">{{ $chapter }}</span>
                    </p>
                @endif
            </div>
        </div>

        @if ($action && $actionLabel)
            <button type="button" wire:click="{{ $action }}"
                class="w-full shrink-0 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600
                       transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:w-auto">
                {{ $actionLabel }}
            </button>
        @else
            <span
                class="absolute right-5 top-5 flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-blue-600 sm:right-6 sm:top-6"
                aria-label="Member verified">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                </svg>
            </span>
        @endif
    </div>
</div>
