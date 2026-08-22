@props(['step', 'totalSteps'])

<div class="mt-5 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <button type="button" wire:click="prevStep"
        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100 {{ $step === 1 ? 'invisible' : '' }}">
        ← <span>Back</span>
    </button>

    <div class="text-center">
        <p class="hidden text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:block">
            Step {{ $step }} of {{ $totalSteps }}
        </p>
        <p class="text-xs text-slate-400">Fields marked <span class="text-red-500">*</span> are required</p>
    </div>

    @if ($step < $totalSteps)
        <button type="button" wire:click="nextStep"
            class="inline-flex items-center gap-2 rounded-xl bg-[#000066] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#00004d] focus:outline-none focus:ring-4 focus:ring-blue-100">
            <span>Next</span> →
        </button>
    @else
        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
            class="inline-flex items-center gap-2 rounded-xl bg-[#000066] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#00004d] focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-50">
            <span wire:loading.remove wire:target="submit">Submit Report</span>
            <span wire:loading wire:target="submit">Submitting…</span>
        </button>
    @endif
</div>