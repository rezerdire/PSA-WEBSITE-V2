<?php

use App\Models\Booth;
use App\Models\BoothAttendance;
use App\Models\Member;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Booth Checker')] class extends Component {
    use WithPagination;

    public string $member_id_no = '';

    /** all | visited | pending */
    public string $filter = 'all';

    public string $search = '';

    /** 10 | 20 */
    public int $perPage = 10;

    public ?string $memberName = null;

    public ?string $memberChapter = null;

    #[Locked]
    public ?string $verifiedId = null;

    public function mount(): void
    {
        // Set by the booth check-in page, so no need to enter the PSA ID again.
        $id = session('booth_verified_id');

        if (!$id) {
            return;
        }

        $member = Member::with('chapter')->find($id);

        if (!$member) {
            session()->forget('booth_verified_id');

            return;
        }

        $this->loadMember($member);
    }

    protected function loadMember(Member $member): void
    {
        $this->verifiedId = $member->member_id_no;
        $this->memberName = preg_replace('/\s+/', ' ', $member->full_name) ?: null;
        $this->memberChapter = $member->chapter?->psa_chapter_desc ?? $member->psa_chapter_code;
    }

    public function check(): void
    {
        $this->validate(['member_id_no' => ['required', 'string', 'max:50']], ['member_id_no.required' => 'Please enter your PSA ID.']);

        // Slow down ID guessing (only failed lookups count)
        $key = 'booth-check-fail:' . request()->ip();

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

        session()->put('booth_verified_id', $member->member_id_no);

        $this->loadMember($member);
        $this->filter = 'all';
        $this->search = '';
        $this->resetPage();
    }

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['all', 'visited', 'pending'], true)) {
            $this->filter = $filter;
            $this->resetPage();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (!in_array($this->perPage, [10, 20, 30], true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function checkAnother(): void
    {
        session()->forget('booth_verified_id');

        $this->reset(['member_id_no', 'verifiedId', 'memberName', 'memberChapter', 'filter', 'search', 'perPage']);
        $this->resetPage();
        $this->resetValidation();
    }

    #[Computed]
    public function visits()
    {
        if (!$this->verifiedId) {
            return collect();
        }

        return BoothAttendance::where('member_id_no', $this->verifiedId)->get()->keyBy('booth_id');
    }

    #[Computed]
    public function stats(): array
    {
        if (!$this->verifiedId) {
            return ['total' => 0, 'visited' => 0, 'pending' => 0, 'percent' => 0];
        }

        $total = Booth::count();
        $visited = Booth::whereIn('booth_id', $this->visits->keys()->all())->count();

        return [
            'total' => $total,
            'visited' => $visited,
            'pending' => $total - $visited,
            'percent' => $total > 0 ? round(($visited / $total) * 100) : 0,
        ];
    }

    #[Computed]
    public function booths()
    {
        if (!$this->verifiedId) {
            return null;
        }

        $visits = $this->visits;
        $visitedIds = $visits->keys()->all();
        $term = trim($this->search);

        $query = Booth::query()->orderBy('booth_id'); // booth_id follows the import order.

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('booth_name', 'like', "%{$term}%")
                    ->orWhere('booth_code', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%");
            });
        }

        if ($this->filter === 'visited') {
            $query->whereIn('booth_id', $visitedIds);
        } elseif ($this->filter === 'pending') {
            $query->whereNotIn('booth_id', $visitedIds);
        }

        return $query->paginate($this->perPage)->through(
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

<div class="w-full" data-booth-checker>
    @php
        $booths = $this->booths;
        $total = $this->stats['total'];
        $visited = $this->stats['visited'];
        $pending = $this->stats['pending'];
        $percent = $this->stats['percent'];
    @endphp

    <x-booth.page-header eyebrow="Event Booths" title="Booth Checker"
        description="Review the booths you have visited and the ones you still have left." />

    @if (!$verifiedId)
        <div class="mx-auto max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
            <div class="p-6 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">Which booths did you visit?</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Enter your PSA ID to see your current booth journey.
                    </p>
                </div>

                <x-booth.member-id-field submit-action="check" submit-label="Check my booths"
                    loading-label="Checking..." />
            </div>
        </div>
    @else
        <div data-booth-result
            class="mx-auto max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md">
            <div class="p-5 sm:p-8">
                <x-booth.member-card :name="$memberName" :member-id="$verifiedId" :chapter="$memberChapter" action="checkAnother"
                    action-label="Check another ID" />
            </div>

            <x-booth.progress-summary :total="$total" :visited="$visited" :percent="$percent">
                <div class="mt-7">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path stroke-linecap="round" d="m20 20-3.5-3.5" />
                            </svg>
                        </div>

                        <input type="search" wire:model.live.debounce.300ms="search" autocomplete="off"
                            placeholder="Search booth name, code or location" aria-label="Search booths"
                            class="block w-full rounded-lg border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm
                                   font-medium text-slate-900 placeholder:font-normal placeholder:text-slate-400
                                   transition duration-200 focus:border-blue-600 focus:bg-white focus:outline-none
                                   focus:ring-2 focus:ring-blue-100">
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <button type="button" wire:click="setFilter('all')"
                            class="rounded-lg border px-3 py-2 text-xs font-bold transition
                                {{ $filter === 'all'
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                            All ({{ $total }})
                        </button>

                        <button type="button" wire:click="setFilter('visited')"
                            class="rounded-lg border px-3 py-2 text-xs font-bold transition
                                {{ $filter === 'visited'
                                    ? 'border-emerald-600 bg-emerald-600 text-white'
                                    : 'border-emerald-100 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                            Visited ({{ $visited }})
                        </button>

                        <button type="button" wire:click="setFilter('pending')"
                            class="rounded-lg border px-3 py-2 text-xs font-bold transition
                                {{ $filter === 'pending'
                                    ? 'border-slate-700 bg-slate-700 text-white'
                                    : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                            Pending ({{ $pending }})
                        </button>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <p class="text-xs font-semibold text-slate-500">
                            @if ($booths->total() > 0)
                                Showing {{ $booths->firstItem() }}–{{ $booths->lastItem() }} of {{ $booths->total() }}
                            @else
                                No results
                            @endif
                        </p>

                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            Per page
                            <select wire:model.live="perPage"
                                class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs font-bold text-slate-700
                                       focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                            </select>
                        </label>
                    </div>

                    <ul class="mt-3 space-y-2">
                        @forelse ($booths as $item)
                            <x-booth.list-item :item="$item" />
                        @empty
                            <li
                                class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                                @if (trim($search) !== '')
                                    No booths match “{{ $search }}”.
                                @elseif ($filter === 'visited')
                                    No booths visited yet.
                                @else
                                    Nothing left. You have visited every booth!
                                @endif
                            </li>
                        @endforelse
                    </ul>

                    @if ($booths->hasPages())
                        <div class="mt-5">
                            {{ $booths->links() }}
                        </div>
                    @endif

                    @if ($total > 0 && $visited === $total)
                        <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-center">
                            <p class="text-base font-extrabold text-blue-900">Amazing! You visited every booth!</p>
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
        let boothCheckerResultVisible = Boolean(document.querySelector('[data-booth-result]'));

        Livewire.hook('morph.updated', ({
            el
        }) => {
            if (!el.closest?.('[data-booth-checker]')) {
                return;
            }

            const resultVisible = Boolean(document.querySelector('[data-booth-result]'));

            if (!boothCheckerResultVisible && resultVisible) {
                requestAnimationFrame(() => window.scrollTo(0, 0));
            }

            boothCheckerResultVisible = resultVisible;
        });
    </script>
@endscript
