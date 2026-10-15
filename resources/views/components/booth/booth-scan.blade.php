<?php

use App\Models\Booth;
use App\Models\BoothAttendance;
use App\Models\Member;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Booth Check-in')] class extends Component
{
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
        $this->booth = Booth::where('booth_code', strtoupper($booth_code))->firstOrFail();
    }

    public function verify(): void
    {
        $this->validate(
            ['member_id_no' => ['required', 'string', 'max:50']],
            ['member_id_no.required' => 'Please enter your PSA ID.']
        );

        // Slow down ID guessing (only failed lookups count)
        $key = 'booth-verify-fail:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->addError('member_id_no', 'Too many attempts. Please wait a minute and try again.');

            return;
        }

        $member = Member::with('chapter')
            ->find(trim($this->member_id_no));

        if (! $member) {
            RateLimiter::hit($key, 60);
            $this->addError('member_id_no', 'PSA ID not found. Please check and try again.');

            return;
        }

        $attendance = BoothAttendance::firstOrCreate(
            [
                'member_id_no' => $member->member_id_no,
                'booth_id'     => $this->booth->booth_id,
            ],
            ['visited_at' => now()]
        );

        $this->verifiedId    = $member->member_id_no;
        $this->memberName    = preg_replace('/\s+/', ' ', $member->full_name) ?: null;
        $this->memberChapter = $this->chapterLabel($member);
        $this->result        = $attendance->wasRecentlyCreated ? 'success' : 'already';
    }

    protected function chapterLabel(Member $member): ?string
    {
        return $member->chapter?->psa_chapter_desc ?? $member->psa_chapter_code;
    }

    #[Computed]
    public function progress()
    {
        if (! $this->verifiedId) {
            return collect();
        }

        $visited = BoothAttendance::where('member_id_no', $this->verifiedId)
            ->pluck('booth_id')
            ->all();

        return Booth::orderBy('booth_type')
            ->orderBy('booth_name')
            ->get()
            ->map(fn (Booth $b) => [
                'name'     => $b->booth_name,
                'location' => $b->location,
                'visited'  => in_array($b->booth_id, $visited),
            ]);
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
                        class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-lg focus:border-amber-500 focus:ring-amber-500"
                    >
                    @error('member_id_no')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-amber-500 px-4 py-3 text-lg font-semibold text-white disabled:opacity-60"
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

                    <p class="text-sm">PSA ID: {{ $verifiedId }}</p>

                    @if ($memberChapter)
                        <p class="text-sm">Chapter: {{ $memberChapter }}</p>
                    @endif
                </div>
            </div>

            <h2 class="mt-6 text-sm font-semibold text-gray-700">Your booth progress</h2>
            <p class="text-xs text-gray-500">
                {{ $this->progress->where('visited', true)->count() }} of {{ $this->progress->count() }} booths visited
            </p>

            <ul class="mt-3 divide-y divide-gray-100">
                @foreach ($this->progress as $item)
                    <li class="flex items-center justify-between py-2 text-sm">
                        <span>
                            <span class="font-medium text-gray-900">{{ $item['name'] }}</span>
                            @if ($item['location'])
                                <span class="block text-xs text-gray-500">{{ $item['location'] }}</span>
                            @endif
                        </span>
                        <span class="{{ $item['visited'] ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $item['visited'] ? '✔ Visited' : 'Not yet' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>