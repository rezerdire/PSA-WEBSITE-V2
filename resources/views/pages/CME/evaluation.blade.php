@extends('layouts.app')

@section('title', 'Annual Convention Evaluation Form')

@section('content')
    <x-about-us-header
        title="Annual Convention Evaluation"
        description="Verify your PSA ID before opening the evaluation form."
    />

    <main class="min-h-[60vh] bg-slate-50 px-4 py-12 sm:px-6">
        <section class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-md sm:p-8">
            @if (($status ?? null) === 'eligible')
                <div class="mb-6 rounded-xl border-2 border-emerald-300 bg-emerald-50 p-4 text-center">
                    <p class="mb-3 font-semibold text-emerald-900">Your evaluation form is ready.</p>
                    <a href="{{ $formUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-700 px-6 py-4 text-lg font-bold text-white shadow-lg transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                        Open Evaluation Form &rarr;
                    </a>
                </div>
            @endif

            <h2 class="mb-2 text-xl font-semibold text-[#000066]">Check your PSA ID</h2>
            <p class="mb-6 text-sm leading-relaxed text-slate-600">
                Enter the PSA ID used for the Annual Convention 2026. We will check our system and the evaluation response sheet before showing the form link.
            </p>

            <form method="POST" action="{{ route('annual-convention-evaluation.check') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="psa_id" class="mb-1 block text-sm font-medium text-slate-700">PSA ID</label>
                    <input
                        id="psa_id"
                        name="psa_id"
                        type="text"
                        value="{{ old('psa_id', $psaId ?? '') }}"
                        maxlength="100"
                        required
                        autocomplete="off"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                    @error('psa_id')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full rounded-lg bg-[#000066] px-5 py-3 font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                    Verify PSA ID
                </button>
            </form>

            @if (isset($masterListRecord))
                <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-950">
                    <p class="text-sm font-medium">Name</p>
                    <p class="mt-1 text-lg font-bold">{{ implode(' ', array_filter([$masterListRecord->first_name, $masterListRecord->middle_name, $masterListRecord->last_name], fn ($part) => filled($part))) }}</p>
                </div>
            @endif

            @if (($status ?? null) === 'not-registered')
                <p role="status" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    We could not find this PSA ID in our system. Check the ID or contact the convention organizers if you believe this is an error.
                </p>
            @elseif (($status ?? null) === 'completed')
                <div role="status" class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    You have already completed the Annual Convention evaluation for this PSA ID. Thank you for participating.
                   
                </div>
                {{--
                <div class="mt-5 flex justify-center">
                    <button
                        type="button"
                        class="group inline-flex w-full max-w-md items-center gap-3 rounded-xl border border-red-700 bg-red-700 px-5 py-4 text-left text-white shadow-lg shadow-red-950/15 transition hover:-translate-y-0.5 hover:bg-red-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2"
                    >
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white/10 text-red-100" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h6l5 5v10.5A1.75 1.75 0 0 1 16.25 21h-9.5A1.75 1.75 0 0 1 5 19.25v-13.5A1.75 1.75 0 0 1 6.75 4H13m0 0v5h5m-6 3v6m0 0-2.5-2.5M12 18l2.5-2.5" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold">Download Certificate of Attendance</span>
                            <span class="mt-0.5 block text-xs text-red-100">Certificate file · PDF</span>
                        </span>
                        <span class="ml-1 rounded-md border border-white/30 px-2 py-1 text-xs font-bold tracking-wide text-white">PDF</span>
                    </button>
                </div>
                --}}
                <div role="note" class="mt-5 rounded-xl border-2 border-amber-400 bg-amber-50 p-5 text-amber-950 shadow-md">
                    <p class="text-lg font-extrabold">Certificate update</p>
                    <p class="mt-1 font-semibold">The certificate will be announced on Facebook once it is available. Please check our Facebook page for updates.</p>
                    <a href="https://www.facebook.com/philippinesocietyofanesthesiologists" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center rounded-lg bg-[#000066] px-4 py-2 font-bold text-white underline decoration-white/60 underline-offset-2 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                        Visit our Facebook page
                    </a>
                </div>
            @elseif (($status ?? null) === 'eligible')
                <div role="status" class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    Your PSA ID is verified, and no evaluation response was found.
                </div>

            @elseif (($status ?? null) === 'not-configured')
                <p role="status" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    Your PSA ID is verified, but the evaluation form is not configured yet. Please contact the convention organizers.
                </p>
            @elseif (($status ?? null) === 'unavailable')
                <p role="status" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    We could not verify the evaluation response sheet right now. The form link is hidden until verification is available; please try again later.
                </p>
            @endif
        </section>
    </main>
@endsection
