@extends('layouts.app')

@section('title', 'Annual Convention Evaluation Form')

@section('content')
    <x-about-us-header
        title="Annual Convention Evaluation"
        description="Verify your annual convention registration before opening the evaluation form."
    />

    <main class="min-h-[60vh] bg-slate-50 px-4 py-12 sm:px-6">
        <section class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-md sm:p-8">
            <h2 class="mb-2 text-xl font-semibold text-[#000066]">Check your registration</h2>
            <p class="mb-6 text-sm leading-relaxed text-slate-600">
                Enter the PSA ID used for your Annual Convention 2026 registration. We will check your registration and the evaluation response sheet before showing the form link.
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

            @if (($status ?? null) === 'not-registered')
                <p role="status" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    We could not find an active Annual Convention registration for this PSA ID. Check the ID or complete your registration first.
                </p>
            @elseif (($status ?? null) === 'completed')
                <div role="status" class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    You have already completed the Annual Convention evaluation for this PSA ID. Thank you for participating.
                   
                </div>
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
            @elseif (($status ?? null) === 'eligible')
                <div role="status" class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    Your registration is verified and no evaluation response was found for this PSA ID.
                </div>
                <div class = "justify-center flex">
                 <a href="{{ $formUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex rounded-lg bg-[#000066] px-5 py-3 font-semibold text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                        Open Evaluation Form
                    </a>
                </div>   

            @elseif (($status ?? null) === 'not-configured')
                <p role="status" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    Your registration is verified, but the evaluation form is not configured yet. Please contact the convention organizers.
                </p>
            @elseif (($status ?? null) === 'unavailable')
                <p role="status" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    We could not verify the evaluation response sheet right now. The form link is hidden until verification is available; please try again later.
                </p>
            @endif
        </section>
    </main>
@endsection
