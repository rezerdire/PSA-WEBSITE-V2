@props([
    'event' => 'PSA Annual Convention 2026',
    'heading' => 'Registration is Now Closed',
    'message' => null,
    'contactEmail' => 'psainc_sec@yahoo.com',
    'primaryLabel' => 'Back to Annual Convention',
    'primaryRoute' => 'convention',
    'secondaryLabel' => 'Return to Homepage',
    'secondaryRoute' => 'home',
])

<section class="bg-white py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm px-6 py-12 sm:px-10 text-center">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#000066]/10" aria-hidden="true">
                    <svg class="h-8 w-8 text-[#000066]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-18 0h18" />
                    </svg>
                </div>

                <h2 class="mt-6 text-2xl sm:text-3xl font-extrabold tracking-tight text-[#000066]">
                    {{ $heading }}
                </h2>

                <p class="mt-3 text-sm sm:text-base text-slate-600 leading-relaxed max-w-xl mx-auto">
                    {{ $message ?? __('Thank you for your interest in the :event. The registration period has officially ended, and we are no longer accepting new registrations.', ['event' => $event]) }}
                </p>

                @if ($contactEmail)
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed max-w-xl mx-auto">
                        For concerns regarding your existing registration, please contact the
                        PSA Secretariat at
                        <a href="mailto:{{ $contactEmail }}" class="font-semibold text-[#000066] hover:underline">
                            {{ $contactEmail }}
                        </a>.
                    </p>
                @endif

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route($primaryRoute) }}"
                        class="w-full sm:w-auto rounded-lg bg-[#000066] px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#000080] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#000066]/50">
                        {{ $primaryLabel }}
                    </a>
                    <a href="{{ route($secondaryRoute) }}"
                        class="w-full sm:w-auto rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-300">
                        {{ $secondaryLabel }}
                    </a>
                </div>

            </div>

        </div>
    </div>
</section>
