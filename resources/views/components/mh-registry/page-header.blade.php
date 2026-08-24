@props(['step', 'totalSteps'])

<div class="mb-8 overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500">
    <div class="px-6 py-7 sm:px-8 lg:px-10">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div
                    class="mb-2 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.15em] text-blue-100">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    National Registry
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white sm:text-3xl">
                    Malignant Hyperthermia Episode Report
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                    Philippine Society of Anesthesiologists - National Malignant Hyperthermia Committee
                </p>
            </div>

            <div class="shrink-0 rounded-2xl bg-white/10 px-5 py-4 text-left sm:text-right">
                <p class="text-[10px] font-bold uppercase tracking-widest text-blue-200">Progress</p>
                <p class="mt-1 text-xl font-black text-white">{{ $step }} <span
                        class="text-sm font-medium text-blue-200">/ {{ $totalSteps }}</span></p>
            </div>
        </div>
    </div>
</div>