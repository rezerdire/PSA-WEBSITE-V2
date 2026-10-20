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

<div class="min-h-dvh w-full bg-slate-50 px-4 py-8 sm:px-6 lg:py-12">
    @php
        $booths = $this->booths;
        $total = $this->stats['total'];
        $visited = $this->stats['visited'];
        $pending = $this->stats['pending'];
        $percent = $this->stats['percent'];
    @endphp

    <div class="mx-auto max-w-xl">

        {{-- Header --}}
        <div class="mb-6 text-center">
            <div
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center
                        rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 6h11M9 12h11M9 18h11M4 6l1 1 2-2M4 12l1 1 2-2M4 18l1 1 2-2" />
                </svg>
            </div>

            <p class="text-xs font-bold uppercase tracking-[0.25em] text-blue-600">
                Event Booths
            </p>

            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Booth Checker
            </h1>
        </div>

        <div
            class="overflow-hidden rounded-3xl border border-slate-200
                    bg-white shadow-xl shadow-slate-200/60">

            @if (!$verifiedId)

                {{-- Lookup form --}}
                <div class="p-6 sm:p-8">
                    <div class="mb-7">
                        <h2 class="text-xl font-bold text-slate-900">Which booths did you visit?</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Enter your PSA ID to see the booths you have already visited
                            and the ones you still have left.
                        </p>
                    </div>

                    <form wire:submit="check" class="space-y-5">
                        <div>
                            <label for="member_id_no" class="mb-2 block text-sm font-bold text-slate-700">
                                PSA ID
                            </label>

                            <input id="member_id_no" type="text" minlength="4" maxlength="4"
                                wire:model="member_id_no" autocomplete="off" autocapitalize="characters"
                                placeholder="Enter your PSA ID"
                                class="block w-full rounded-xl border bg-slate-50 px-4 py-4
                                       text-base font-medium text-slate-900
                                       placeholder:font-normal placeholder:text-slate-400
                                       transition duration-200
                                       focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-100
                                       {{ $errors->has('member_id_no')
                                           ? 'border-red-400 focus:border-red-500'
                                           : 'border-slate-200 focus:border-blue-400' }}"
                                @if ($errors->has('member_id_no')) aria-invalid="true" @endif>

                            @error('member_id_no')
                                <div role="alert"
                                    class="mt-3 rounded-xl border border-red-100 bg-red-50 p-3
                                            text-sm text-red-700">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="check"
                            class="flex w-full items-center justify-center rounded-xl bg-blue-600
                                   px-5 py-4 text-base font-bold text-white
                                   shadow-lg shadow-blue-100 transition duration-200
                                   hover:bg-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-200
                                   disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="check">Check my booths</span>
                            <span wire:loading wire:target="check">Checking...</span>
                        </button>
                    </form>
                </div>
            @else
                {{-- Member --}}
                <div class="border-b border-slate-100 p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
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
                                <span class="font-semibold text-slate-800">{{ $verifiedId }}</span>
                            </p>

                            @if ($memberChapter)
                                <p class="mt-1 text-sm text-slate-600">
                                    Chapter:
                                    <span class="font-semibold text-slate-800">{{ $memberChapter }}</span>
                                </p>
                            @endif
                        </div>

                        <button type="button" wire:click="checkAnother"
                            class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-xs
                                   font-bold text-slate-600 transition hover:bg-slate-50">
                            Check another ID
                        </button>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="px-5 py-6 sm:px-8">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">Your booth journey</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $percent }}% completed</p>
                        </div>

                        <p class="text-2xl font-extrabold tabular-nums text-slate-900">
                            {{ $visited }}<span class="text-base font-semibold text-slate-400"> /
                                {{ $total }}</span>
                        </p>
                    </div>

                    <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-700"
                            style="width: {{ $percent }}%" role="progressbar" aria-label="Booth visit progress"
                            aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}"></div>
                    </div>

                    {{-- Search --}}
                    <div class="relative mt-6">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7" />
                                <path stroke-linecap="round" d="m20 20-3.5-3.5" />
                            </svg>
                        </div>

                        <input type="search" wire:model.live.debounce.300ms="search" autocomplete="off"
                            placeholder="Search booth name, code or location"
                            aria-label="Search booths"
                            class="block w-full rounded-xl border border-slate-200 bg-slate-50
                                   py-3 pl-10 pr-4 text-sm font-medium text-slate-900
                                   placeholder:font-normal placeholder:text-slate-400
                                   transition duration-200
                                   focus:border-blue-400 focus:bg-white focus:outline-none
                                   focus:ring-4 focus:ring-blue-100">
                    </div>

                    {{-- Filter --}}
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <button type="button" wire:click="setFilter('all')"
                            class="rounded-lg px-3 py-2 text-xs font-bold transition
                                       {{ $filter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            All ({{ $total }})
                        </button>

                        <button type="button" wire:click="setFilter('visited')"
                            class="rounded-lg px-3 py-2 text-xs font-bold transition
                                       {{ $filter === 'visited' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                            Visited ({{ $visited }})
                        </button>

                        <button type="button" wire:click="setFilter('pending')"
                            class="rounded-lg px-3 py-2 text-xs font-bold transition
                                       {{ $filter === 'pending' ? 'bg-blue-500 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                            Pending ({{ $pending }})
                        </button>
                    </div>

                    {{-- Result count + per page --}}
                    <div class="mt-4 flex items-center justify-between gap-3">
                        <p class="text-xs font-semibold text-slate-500">
                            @if ($booths->total() > 0)
                                Showing {{ $booths->firstItem() }}–{{ $booths->lastItem() }}
                                of {{ $booths->total() }}
                            @else
                                No results
                            @endif
                        </p>

                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            Per page
                            <select wire:model.live="perPage"
                                class="rounded-lg border border-slate-200 bg-white px-2 py-1.5
                                       text-xs font-bold text-slate-700 focus:border-blue-400
                                       focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                            </select>
                        </label>
                    </div>

                    {{-- Booth list --}}
                    <ul class="mt-3 space-y-2">
                        @forelse ($booths as $item)
                            <li wire:key="booth-{{ $item['id'] }}"
                                class="flex items-center gap-3 rounded-xl border p-3
                                       {{ $item['visited_at'] ? 'border-emerald-100 bg-emerald-50/50' : 'border-slate-100 bg-white' }}">

                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                            {{ $item['visited_at'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                    @if ($item['visited_at'])
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="4" y="4" width="16" height="16" rx="3" />
                                        </svg>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="break-words text-sm font-bold
                                              {{ $item['visited_at'] ? 'text-emerald-900' : 'text-slate-800' }}">
                                        {{ $item['name'] }}
                                    </p>

                                    <p class="mt-0.5 break-words text-xs text-slate-500">
                                        {{ $item['code'] }}
                                        @if ($item['location'])
                                            <span class="px-0.5">·</span>{{ $item['location'] }}
                                        @endif
                                    </p>
                                </div>

                                @if ($item['visited_at'])
                                    <span class="shrink-0 text-right text-[11px] font-bold text-emerald-700">
                                        Visited
                                        <span class="block font-medium text-emerald-600">
                                            {{ $item['visited_at']->format('M j, g:i A') }}
                                        </span>
                                    </span>
                                @else
                                    <span
                                        class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1
                                                 text-[11px] font-bold text-slate-500">
                                        Pending
                                    </span>
                                @endif
                            </li>
                        @empty
                            <li
                                class="rounded-xl border border-dashed border-slate-200 p-6
                                       text-center text-sm text-slate-500">
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

                    {{-- Pagination --}}
                    @if ($booths->hasPages())
                        <div class="mt-5">
                            {{ $booths->links() }}
                        </div>
                    @endif

                    @if ($total > 0 && $visited === $total)
                        <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-center">
                            <p class="text-base font-extrabold text-blue-900">
                                Amazing! You visited every booth!
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