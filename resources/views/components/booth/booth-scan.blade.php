<?php

use App\Models\Booth;
use App\Models\BoothAttendance;
use App\Models\Member;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Booth Check-in')] class extends Component {
    public Booth $booth;

    public string $member_id_no = '';

    /** success | already | null */
    public ?string $result = null;

    public ?string $memberName = null;

    public ?string $memberChapter = null;

    #[Locked]
    public ?string $verifiedId = null;

    public function mount(string $booth_code): void
    {
        // The QR holds the generated code (e.g. SUITE1BP), not the display booth_code.
        $this->booth = Booth::where('booth_generated_code', strtoupper(trim($booth_code)))->firstOrFail();
    }

    public function verify(): void
    {
        $this->validate(['member_id_no' => ['required', 'string', 'max:50']], ['member_id_no.required' => 'Please enter your PSA ID.']);

        // Slow down ID guessing (only failed lookups count)
        $key = 'booth-verify-fail:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->addError('member_id_no', 'Too many attempts. Please wait a minute and try again.');

            return;
        }

        $member = Member::with('chapter')->find(trim($this->member_id_no));

        if (!$member) {
            RateLimiter::hit($key, 60);
            $this->addError('member_id_no', 'PSA ID not found. Please check and try again.');

            return;
        }

        $attendance = BoothAttendance::firstOrCreate(
            [
                'member_id_no' => $member->member_id_no,
                'booth_id' => $this->booth->booth_id,
            ],
            ['visited_at' => now()],
        );

        // Remember the verified member so the Booth Checker page needs no re-entry.
        session()->put('booth_verified_id', $member->member_id_no);

        $this->verifiedId = $member->member_id_no;
        $this->memberName = preg_replace('/\s+/', ' ', $member->full_name) ?: null;
        $this->memberChapter = $this->chapterLabel($member);
        $this->result = $attendance->wasRecentlyCreated ? 'success' : 'already';
    }

    protected function chapterLabel(Member $member): ?string
    {
        return $member->chapter?->psa_chapter_desc ?? $member->psa_chapter_code;
    }

    #[Computed]
    public function progress()
    {
        if (!$this->verifiedId) {
            return collect();
        }

        $visited = BoothAttendance::where('member_id_no', $this->verifiedId)->pluck('booth_id')->all();

        // booth_id follows the import order (the order of the spreadsheet).
        return Booth::orderBy('booth_id')->get()->map(
            fn(Booth $b) => [
                'name' => $b->booth_name,
                'code' => $b->booth_code,
                'location' => $b->location,
                'visited' => in_array($b->booth_id, $visited),
            ],
        );
    }
};
?>

<div class="mx-auto max-w-md p-5">
    <div class="rounded-2xl bg-white p-6 shadow">
        <p class="text-center text-xs font-semibold uppercase tracking-widest text-gray-500">
            Booth Check-in
        </p>
        <h1 class="mt-1 text-center text-2xl font-bold text-gray-900">{{ $booth->booth_name }}</h1>
        <p class="mt-1 text-center text-sm text-gray-500">
            {{ $booth->booth_type }}@if($booth->location) &bull; {{ $booth->location }}@endif
        </p>

        @if (! $result)
            <form wire:submit="verify" class="mt-6 space-y-4">
                <div>
                    <label for="member_id_no" class="block text-sm font-medium text-gray-700">
                        Enter your PSA ID to confirm your visit
                    </label>
                    <input
                        id="member_id_no"
                        type="text"
                        wire:model="member_id_no"
                        autocomplete="off"
                        placeholder="PSA ID"
                        class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-lg focus:border-blue-5 00 focus:ring-blue-500"
                    >
                    @error('member_id_no')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
<div class="min-h-dvh w-full bg-slate-50 px-4 py-8 sm:px-6 lg:py-12">
    @php
        $boothProgress = $this->progress;
        $totalBooths = $boothProgress->count();
        $visitedBooths = $boothProgress->where('visited', true)->count();
        $progressPercent = $totalBooths > 0 ? round(($visitedBooths / $totalBooths) * 100) : 0;
    @endphp

    <div class="mx-auto max-w-xl">

        {{-- Event / Booth Header --}}
        <div class="mb-6 text-center">
            <div
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center
                        rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                    <circle cx="12" cy="12" r="9" />
                </svg>
            </div>

            <p class="text-xs font-bold uppercase tracking-[0.25em] text-blue-600">
                Event Booth Check-in
            </p>

            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                {{ $booth->booth_name }}
            </h1>

            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full
                             border border-slate-200 bg-white px-3 py-1.5
                             text-xs font-semibold text-slate-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-500" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 7V4h3M17 4h3v3M20 17v3h-3M7 20H4v-3" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 8h3v3H8zM14 8h2v2h-2zM14 14h3v3h-3z" />
                    </svg>
                    {{ $booth->booth_code }}
                </span>

                @if ($booth->location)
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full
                                 border border-slate-200 bg-white px-3 py-1.5
                                 text-xs font-semibold text-slate-600 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-500" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>
                        {{ $booth->location }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Main Card --}}
        <div
            class="overflow-hidden rounded-3xl border border-slate-200
                    bg-white shadow-xl shadow-slate-200/60">
            @if (!$result)

                {{-- Check-in Form --}}
                <div class="p-6 sm:p-8">

                    <div class="mb-7">
                        <h2 class="text-xl font-bold text-slate-900">
                            Welcome!
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Enter your PSA ID to check in and record your visit to this booth.
                        </p>
                    </div>

                    <form wire:submit="verify" class="space-y-5">
                        <div>
                            <label for="member_id_no" class="mb-2 block text-sm font-bold text-slate-700">
                                PSA ID
                            </label>

                            <div class="relative">
                                <div
                                    class="pointer-events-none absolute inset-y-0 left-0
                                            flex items-center pl-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="8" r="4" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5 21v-2a7 7 0 0 1 14 0v2" />
                                    </svg>
                                </div>

                                <input id="member_id_no" type="text" wire:model="member_id_no" autocomplete="off"
                                    autocapitalize="characters" placeholder="Enter your PSA ID" minlength="4"
                                    maxlength="4" aria-describedby="member-id-help"
                                    @error('member_id_no')
                                        aria-invalid="true"
                                    @enderror
                                    class="block w-full rounded-xl border bg-slate-50
                                           py-4 pl-12 pr-4 text-base font-medium
                                           text-slate-900 placeholder:font-normal
                                           placeholder:text-slate-400
                                           transition duration-200
                                           focus:bg-white focus:outline-none
                                           focus:ring-4 focus:ring-blue-100
                                           @error('member_id_no')
                                               border-red-400 focus:border-red-500
                                           @else
                                               border-slate-200 focus:border-blue-400
                                           @enderror">
                            </div>

                            @error('member_id_no')
                                <div role="alert"
                                    class="mt-3 flex items-start gap-2 rounded-xl
                                            border border-red-100 bg-red-50 p-3
                                            text-sm text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                                    </svg>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="verify"
                            class="group flex w-full items-center justify-center gap-2
                                   rounded-xl bg-blue-600 px-5 py-4
                                   text-base font-extrabold text-white
                                   shadow-lg shadow-blue-100
                                   transition duration-200
                                   hover:bg-blue-500 hover:shadow-blue-200
                                   focus:outline-none focus:ring-4 focus:ring-blue-200
                                   disabled:cursor-wait disabled:opacity-60">
                            <svg wire:loading.remove wire:target="verify" xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                                <circle cx="12" cy="12" r="9" />
                            </svg>

                            <svg wire:loading wire:target="verify" xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke-opacity=".25" />
                                <path stroke-linecap="round" d="M22 12a10 10 0 0 0-10-10" />
                            </svg>

                            <span wire:loading.remove wire:target="verify">
                                Verify &amp; Check In
                            </span>

                            <span wire:loading wire:target="verify">
                                Verifying membership...
                            </span>
                        </button>
                    </form>

                </div>
            @else
                {{-- Result Header --}}
                <div class="px-6 pb-6 pt-8 text-center sm:px-8">

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3 text-lg font-semibold text-white disabled:opacity-60"
                >
                    <span wire:loading.remove>Verify</span>
                    <span wire:loading>Checking...</span>
                </button>
            </form>
        @else
            <div class="mt-6 rounded-xl p-4 text-center {{ $result === 'success' ? 'bg-green-50 text-green-800' : 'bg-blue-50 text-blue-800' }}">
                <p class="text-lg font-semibold">
                    {{ $result === 'success' ? 'Visit recorded!' : 'You already checked in here.' }}
                </p>

                <div class="mt-3 border-t border-black/10 pt-3">
                    @if ($memberName)
                        <p class="text-base font-bold">{{ $memberName }}</p>
                    @endif
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center
                                rounded-full
                                {{ $result === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }}">
                        @if ($result === 'success')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                            </svg>
                        @endif
                    </div>

                    <p
                        class="mt-5 text-xs font-bold uppercase tracking-[0.2em]
                              {{ $result === 'success' ? 'text-emerald-600' : 'text-blue-600' }}">
                        {{ $result === 'success' ? 'Check-in successful' : 'Already checked in' }}
                    </p>

                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                        {{ $result === 'success' ? 'Visit recorded!' : 'You’re all set!' }}
                    </h2>

                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">
                        {{ $result === 'success'
                            ? 'Your visit to this booth has been successfully recorded. Thank you!'
                            : 'Your visit to this booth has already been recorded. You’re good to go!' }}
                    </p>

                    {{-- Link to Booth Checker (no PSA ID re-entry needed) --}}
                    <a href="{{ route('booth.checker') }}" wire:navigate
                        class="mt-6 inline-flex w-full items-center justify-center gap-2
                               rounded-xl bg-slate-900 px-5 py-4 text-base font-extrabold
                               text-white shadow-lg shadow-slate-200 transition duration-200
                               hover:bg-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 6h11M9 12h11M9 18h11M4 6l1 1 2-2M4 12l1 1 2-2M4 18l1 1 2-2" />
                        </svg>
                        Check my visited &amp; pending booths
                    </a>
                </div>

                {{-- Member Information --}}
                <div
                    class="mx-5 mb-6 rounded-2xl border p-5 sm:mx-8
                            {{ $result === 'success' ? 'border-emerald-100 bg-emerald-50/70' : 'border-blue-100 bg-blue-50/70' }}">

                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center
                                    rounded-xl bg-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-600"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="8" r="4" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 21v-2a7 7 0 0 1 14 0v2" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Verified member
                            </p>

                            @if ($memberName)
                                <p class="mt-1 break-words text-lg font-extrabold text-slate-900">
                                    {{ $memberName }}
                                </p>
                            @endif

                            <p class="mt-1 text-sm text-slate-600">
                                PSA ID:
                                <span class="font-semibold text-slate-800">
                                    {{ $verifiedId }}
                                </span>
                            </p>

                            @if ($memberChapter)
                                <p class="mt-1 text-sm text-slate-600">
                                    Chapter:
                                    <span class="font-semibold text-slate-800">
                                        {{ $memberChapter }}
                                    </span>
                                </p>
                            @endif
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 shrink-0
                                    {{ $result === 'success' ? 'text-emerald-600' : 'text-blue-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                    </div>
                </div>

                {{-- Booth Progress --}}
                <div class="border-t border-slate-100 px-5 py-6 sm:px-8">

                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Your booth journey
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Keep exploring the event!
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-2xl font-extrabold tabular-nums text-slate-900">
                                {{ $visitedBooths }}<span class="text-base font-semibold text-slate-400">
                                    / {{ $totalBooths }}
                                </span>
                            </p>
                            <p class="text-xs font-semibold text-slate-500">
                                Booths visited
                            </p>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-700"
                            style="width: {{ $progressPercent }}%" role="progressbar"
                            aria-label="Booth visit progress" aria-valuemin="0" aria-valuemax="100"
                            aria-valuenow="{{ $progressPercent }}"></div>
                    </div>

                    <p class="mt-2 text-right text-xs font-semibold text-slate-500">
                        {{ $progressPercent }}% completed
                    </p>

                    {{-- Progress Summary --}}
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div
                            class="rounded-xl border border-emerald-100
                                    bg-emerald-50 p-3">
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex h-7 w-7 items-center justify-center
                                             rounded-lg bg-emerald-100 text-emerald-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                    </svg>
                                </span>

                                <span class="text-xs font-semibold text-emerald-800">
                                    Visited
                                </span>
                            </div>

                            <p class="mt-2 text-2xl font-extrabold text-emerald-800">
                                {{ $visitedBooths }}
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-slate-200
                                    bg-slate-50 p-3">
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex h-7 w-7 items-center justify-center
                                             rounded-lg bg-slate-200 text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" d="M12 7v5l3 2" />
                                    </svg>
                                </span>

                                <span class="text-xs font-semibold text-slate-600">
                                    Pending
                                </span>
                            </div>

                            <p class="mt-2 text-2xl font-extrabold text-slate-800">
                                {{ $totalBooths - $visitedBooths }}
                            </p>
                        </div>
                    </div>

                    {{-- Booth List --}}
                    <div class="mt-7">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <h4 class="text-sm font-bold text-slate-800">
                                All event booths
                            </h4>

                            <span class="text-xs text-slate-500">
                                In event order
                            </span>
                        </div>

                        <ul class="space-y-2">
                            @foreach ($boothProgress as $item)
                                <li wire:key="booth-progress-{{ $loop->index }}"
                                    class="flex items-center gap-3 rounded-xl border p-3
                                           transition-colors
                                           {{ $item['visited'] ? 'border-emerald-100 bg-emerald-50/50' : 'border-slate-100 bg-white' }}">

                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center
                                                justify-center rounded-xl
                                                {{ $item['visited'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">

                                        @if ($item['visited'])
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m5 12 4 4L19 6" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8">
                                                <rect x="4" y="4" width="16" height="16" rx="3" />
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="break-words text-sm font-bold
                                                  {{ $item['visited'] ? 'text-emerald-900' : 'text-slate-800' }}">
                                            {{ $item['name'] }}
                                        </p>

                                        <p class="mt-0.5 break-words text-xs text-slate-500">
                                            {{ $item['code'] }}
                                            @if ($item['location'])
                                                <span class="px-0.5">·</span>
                                                {{ $item['location'] }}
                                            @endif
                                        </p>
                                    </div>

                                    <span
                                        class="shrink-0 rounded-full px-2.5 py-1
                                                 text-[11px] font-bold
                                                 {{ $item['visited'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $item['visited'] ? 'Visited' : 'Pending' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Completion Message --}}
                    @if ($totalBooths > 0 && $visitedBooths === $totalBooths)
                        <div
                            class="mt-6 rounded-2xl border border-blue-200
                                    bg-blue-50 p-5 text-center">
                            <p class="text-base font-extrabold text-blue-900">
                                You visited every booth!
                            </p>
                            <p class="mt-1 text-sm text-blue-800">
                                Thank you for participating in the event.
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

    </div>
</div>