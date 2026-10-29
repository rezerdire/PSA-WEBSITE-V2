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

        $visits = BoothAttendance::where('member_id_no', $this->verifiedId)->get()->keyBy('booth_id');

        // booth_id follows the import order (the order of the spreadsheet).
        return Booth::orderBy('booth_id')->get()->map(
            fn(Booth $b) => [
                'id' => $b->booth_id,
                'name' => $b->booth_name,
                'code' => $b->booth_code,
                'location' => $b->location,
                'visited_at' => $visits->get($b->booth_id)?->visited_at,
            ],
        );
    }
};
?>

<div class="w-full" data-booth-scan>
    @php
        $boothProgress = $this->progress;
        $totalBooths = $boothProgress->count();
        $visitedBooths = $boothProgress->filter(fn($item) => filled($item['visited_at']))->count();
        $progressPercent = $totalBooths > 0 ? round(($visitedBooths / $totalBooths) * 100) : 0;
    @endphp

    <x-booth.page-header eyebrow="Event Booth Check-in" :title="$booth->booth_name"
        description="Enter your PSA ID to record this visit and track your event progress.">
        <span
            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-sm font-semibold text-blue-50">
            <span class="h-1.5 w-1.5 rounded-full bg-blue-200"></span>
            {{ $booth->booth_code }}
        </span>

        @if ($booth->location)
            <span
                class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-sm font-semibold text-blue-50">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-200"></span>
                {{ $booth->location }}
            </span>
        @endif
    </x-booth.page-header>

    @if (!$result)
        <div class="mx-auto max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
            <div class="p-6 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">Welcome!</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Your check-in will be saved to this booth and added to your event journey.
                    </p>
                </div>

                <x-booth.member-id-field submit-action="verify" :submit-label="'Verify & Check In'"
                    loading-label="Verifying membership..." />
            </div>
        </div>
    @else
        <div data-booth-result
            class="mx-auto max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
            <div class="px-6 pb-6 pt-8 text-center sm:px-8">
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full
                        {{ $result === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
                    @if ($result === 'success')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                        </svg>
                    @endif
                </div>

                <p
                    class="mt-4 text-xs font-bold uppercase tracking-[0.2em]
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

                <a href="{{ route('booth.checker') }}" wire:navigate
                    class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-3
                           text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-blue-700
                           focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 6h11M9 12h11M9 18h11M4 6l1 1 2-2M4 12l1 1 2-2M4 18l1 1 2-2" />
                    </svg>
                    Check my visited &amp; pending booths
                </a>
            </div>

            <div class="mx-5 mb-6 sm:mx-8">
                <x-booth.member-card :name="$memberName" :member-id="$verifiedId" :chapter="$memberChapter" />
            </div>

            <x-booth.progress-summary :total="$totalBooths" :visited="$visitedBooths" :percent="$progressPercent">
                <div class="mt-7">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold text-slate-800">All event booths</h3>
                        <span class="text-xs text-slate-500">In event order</span>
                    </div>

                    <ul class="space-y-2">
                        @foreach ($boothProgress as $item)
                            <x-booth.list-item :item="$item" />
                        @endforeach
                    </ul>

                    @if ($totalBooths > 0 && $visitedBooths === $totalBooths)
                        <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-center">
                            <p class="text-base font-extrabold text-blue-900">You visited every booth!</p>
                            <p class="mt-1 text-sm text-blue-800">Thank you for participating in the event.</p>
                        </div>
                    @endif
                </div>
            </x-booth.progress-summary>
        </div>
    @endif
</div>

@script
    <script>
        let boothScanResultVisible = Boolean(document.querySelector('[data-booth-result]'));

        Livewire.hook('morph.updated', ({
            el
        }) => {
            if (!el.closest?.('[data-booth-scan]')) {
                return;
            }

            const resultVisible = Boolean(document.querySelector('[data-booth-result]'));

            if (!boothScanResultVisible && resultVisible) {
                requestAnimationFrame(() => window.scrollTo(0, 0));
            }

            boothScanResultVisible = resultVisible;
        });
    </script>
@endscript
