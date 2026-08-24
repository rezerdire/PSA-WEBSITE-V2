@props(['referenceNo', 'patientName', 'hospital', 'episodeDate', 'rawScore', 'gradingRank', 'finalDisposition'])

<div class="max-w-3xl mx-auto px-4 py-10 sm:px-6 lg:px-8" id="registry-success">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50">
        <div class="bg-[#000066] px-6 py-8 text-center sm:px-10">
            <div
                class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-white/15 ring-8 ring-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-blue-100">National MH Registry</p>
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Report Submitted Successfully</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-blue-100">
                The MH episode has been recorded in the National Malignant Hyperthermia Registry.
            </p>
        </div>

        <div class="p-6 sm:p-8">
            <div class="mb-6 rounded-2xl border border-blue-100 bg-blue-50/60 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Reference Number</p>
                <p class="mt-1 text-2xl font-black tracking-wide text-[#000066]">{{ $referenceNo }}</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Report Summary</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ([
                            ['Reference No.', $referenceNo],
                            ['Patient', $patientName],
                            ['Hospital', $hospital],
                            ['Episode Date', $episodeDate],
                            ['Grading Score', $rawScore . ' pts — Rank ' . $gradingRank['rank'] . ' (' . $gradingRank['label'] . ')'],
                            ['Final Disposition', $finalDisposition],
                        ] as [$label, $value])
                        <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-[180px_1fr] sm:gap-4">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</span>
                            <span class="text-sm font-semibold text-slate-700">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-8 flex justify-center">
                <a href="{{ url('/') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-[#000066] px-7 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#00004d] focus:outline-none focus:ring-4 focus:ring-blue-100">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>