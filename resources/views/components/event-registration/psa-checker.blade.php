
<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Member;

new class extends Component
{
    public string $lastName = '';
    public string $email = '';

    #[Computed]
    public function results()
    {
        if (strlen(trim($this->lastName)) < 2) {
            return collect();
        }

        return Member::where('mem_last_name', 'like', '%' . trim($this->lastName) . '%')
            ->orderBy('mem_last_name')
            ->get(['member_id_no', 'mem_last_name', 'mem_first_name', 'mem_middle_name']);
    }

    #[Computed]
    public function emailResults()
    {
        if (strlen(trim($this->email)) < 3) {
            return collect();
        }

        return Member::where('mem_email_address', 'like', '%' . trim($this->email) . '%')
            ->orderBy('mem_email_address')
            ->get(['member_id_no', 'mem_last_name', 'mem_first_name', 'mem_middle_name', 'mem_email_address']);
    }
};
?>

{{-- alpine close default --}}
<div class="bg-white rounded-2xl shadow-md overflow-hidden mb-6" x-data="{ open: false, tab: 'lastName' }">
    {{-- line header --}}
<div class="h-1.5 bg-gradient-to-r from-[#000066] to-[#0000aa]"></div>
    <div class="p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-base text-[#000066]" >PSA ID Number Checker</h2>
                <p class="text-gray-400 text-xs mt-0.5">Search your last name or email to find your PSA ID</p>
            </div>

            {{-- button switch design --}}
            <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg text-white transition hover:opacity-90 bg-[#000066]">
                {{-- ICON --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
                </svg>
                <span x-text="open ? 'Close Checker' : 'Open Checker'"></span>
            </button>

        </div>

        {{-- id checker form --}}
        <div x-show="open" x-transition class="mt-5">

            {{-- tab switcher --}}
            <div class="flex gap-2 mb-4 border-b border-gray-100">
                <button type="button" @click="tab = 'lastName'"
                    :class="tab === 'lastName' ? 'border-[#000066] text-[#000066]' : 'border-transparent text-gray-400'"
                    class="px-3 py-2 text-xs font-semibold uppercase tracking-wide border-b-2 transition">
                    Search by Last Name
                </button>
                <button type="button" @click="tab = 'email'"
                    :class="tab === 'email' ? 'border-[#000066] text-[#000066]' : 'border-transparent text-gray-400'"
                    class="px-3 py-2 text-xs font-semibold uppercase tracking-wide border-b-2 transition">
                    Search by Email
                </button>
            </div>

            {{-- last name search panel --}}
            <div x-show="tab === 'lastName'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Enter Last Name
                    </label>

                    <div class="relative">
                        <input type="text" wire:model.live.debounce.400ms="lastName"
                            placeholder="e.g. Vacaro"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                        <div wire:loading wire:target="lastName" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                            Searching...
                        </div>
                    </div>

                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Results
                    </label>
                    @if(strlen(trim($lastName)) < 2)
                        <div class="border border-dashed border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-400 text-center">
                            Results will appear here
                        </div>

                    @elseif($this->results->isEmpty())
                        <div class="border border-dashed border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-400 text-center">
                            No members found for "{{ $lastName }}"
                        </div>

                    @else
                        <ul class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-48 overflow-y-auto">
                            @foreach($this->results as $member)
                                <li class="px-3 py-2.5 flex items-center justify-between gap-2">
                                    <span class="text-sm text-gray-700 truncate">
                                        {{ $member->mem_last_name }}, {{ $member->mem_first_name }} {{ $member->mem_middle_name }}
                                    </span>
                                 <span class="text-xs font-mono font-semibold shrink-0 px-2 py-0.5 rounded bg-[#e8e8f7] text-[#000066]">
                                        {{ $member->member_id_no }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                </div>
            </div>

            {{-- email search panel --}}
            <div x-show="tab === 'email'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Enter Email Address
                    </label>

                    <div class="relative">
                        <input type="text" wire:model.live.debounce.400ms="email"
                            placeholder="e.g. juan@example.com"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                        <div wire:loading wire:target="email" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                            Searching...
                        </div>
                    </div>

                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Results
                    </label>
                    @if(strlen(trim($email)) < 3)
                        <div class="border border-dashed border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-400 text-center">
                            Results will appear here
                        </div>

                    @elseif($this->emailResults->isEmpty())
                        <div class="border border-dashed border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-400 text-center">
                            No members found for "{{ $email }}"
                        </div>

                    @else
                        <ul class="border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-48 overflow-y-auto">
                            @foreach($this->emailResults as $member)
                                <li class="px-3 py-2.5 flex flex-col gap-0.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm text-gray-700 truncate">
                                            FULL NAME: {{ $member->mem_last_name }}, {{ $member->mem_first_name }} {{ $member->mem_middle_name }}
                                        </span>
                                        <span class="text-xs font-mono font-semibold shrink-0 px-2 py-0.5 rounded bg-[#e8e8f7] text-[#000066]">
                                            PSA ID: {{ $member->member_id_no }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-400 truncate">{{ $member->mem_email_address }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>