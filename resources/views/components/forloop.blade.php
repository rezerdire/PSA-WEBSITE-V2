<?php
// temp file for testing data seeder
use App\Models\Member;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'members' => Member::when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('mem_first_name', 'like', "%{$this->search}%")
                          ->orWhere('mem_last_name', 'like', "%{$this->search}%")
                          ->orWhere('member_id_no', 'like', "%{$this->search}%");
                    });
                })
                ->orderBy('member_id_no', 'asc')
                ->paginate(20),
        ];
    }
};
?>
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Members</h1>

        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Search by name or ID..."
            class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10"
        >
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Member ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Chapter Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Mobile No.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($members as $member)
                    <tr wire:key="member-{{ $member->member_id_no }}" class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm font-mono text-slate-600">{{ $member->member_id_no }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-900">
                            {{ $member->mem_last_name }}, {{ $member->mem_first_name }} {{ $member->mem_middle_name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $member->psa_chapter_code }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $member->mem_email_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $member->mem_mobile_no1 ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">
                            No members found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $members->links() }}
    </div>
</div>
