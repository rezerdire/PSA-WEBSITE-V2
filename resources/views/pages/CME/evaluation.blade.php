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
                <p role="status" class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    Our response sheet shows that this PSA ID has already completed the evaluation.
                </p>
            @elseif (($status ?? null) === 'eligible')
                <div role="status" class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    Your registration is verified and no evaluation response was found for this PSA ID.
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
