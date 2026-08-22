@props(['step', 'totalSteps'])

<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="hidden items-center md:flex">
        @foreach ([
                1 => 'Header',
                2 => 'Demographics',
                3 => 'Clinical Event',
                4 => 'Management',
                5 => 'Diagnostics',
                6 => 'Facility',
            ] as $num => $label)
            <div class="flex flex-1 items-center">
                <button type="button" wire:click="goToStep({{ $num }})"
                    class="group flex min-w-0 items-center gap-3 text-left">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-xs font-black transition
                                    {{ $step > $num ? 'border-[#000066] bg-[#000066] text-white' : ($step === $num ? 'border-[#000066] bg-blue-50 text-[#000066]' : 'border-slate-200 bg-white text-slate-400') }}">
                        @if ($step > $num)
                            ✓
                        @else
                            {{ $num }}
                        @endif
                    </span>
                    <span class="hidden min-w-0 lg:block">
                        <span
                            class="block text-[10px] font-bold uppercase tracking-wider {{ $step === $num ? 'text-[#000066]' : 'text-slate-400' }}">Step
                            {{ $num }}</span>
                        <span
                            class="block truncate text-xs font-semibold {{ $step === $num ? 'text-slate-800' : 'text-slate-500' }}">{{ $label }}</span>
                    </span>
                </button>
                @if ($num < $totalSteps)
                    <div class="mx-3 h-px flex-1 {{ $step > $num ? 'bg-[#000066]' : 'bg-slate-200' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between md:hidden">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current section</p>
            <p class="mt-1 text-sm font-bold text-slate-800">
                {{ [
    1 => 'Episode & Reporting Facility',
    2 => 'Patient Demographics',
    3 => 'Clinical Event Data',
    4 => 'Management',
    5 => 'Diagnostics & Outcome',
    6 => 'Facility Readiness',
][$step] }}
            </p>
        </div>
        <div class="text-right">
            <p class="text-xl font-black text-[#000066]">{{ $step }}/{{ $totalSteps }}</p>
        </div>
    </div>

    <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100">
        <div class="h-full rounded-full bg-[#000066] transition-all duration-300"
            style="width: {{ (($step - 1) / ($totalSteps - 1)) * 100 }}%"></div>
    </div>
</div>