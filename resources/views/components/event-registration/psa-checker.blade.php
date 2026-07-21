<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Member;

new class extends Component
{
    public string $lastName = '';

    #[Computed] 
    // normally u store it on array/variable after getting a request from database and retrieving the data
    //ex: public $results = [] then method for search(){$this->results = ModelName::all() or use return; }
    // traditionally you store data $this->results = data;
    // by using computed you store the logic to get the data;
    public function results()
    {

        // Condition it will only returns a value once you type 2 characters
        if (strlen(trim($this->lastName)) < 2) {    
            return collect();
        }
        // retrieving the last name
        //without the computed: $this->results() = query
        //trim remove the white space before requesting query
        return Member::where('mem_last_name', 'like', '%' . trim($this->lastName) . '%')
            ->orderBy('mem_last_name')
            ->get(['member_id_no', 'mem_last_name', 'mem_first_name', 'mem_middle_name']);
    }
};
?>

{{-- alpine close default --}}
<div class="bg-white rounded-2xl shadow-md overflow-hidden mb-6" x-data="{ open: false }">  
    {{-- line header --}}
<div class="h-1.5 bg-gradient-to-r from-[#000066] to-[#0000aa]"></div> 
    <div class="p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-base text-[#000066]" >PSA ID Number Checker</h2>
                <p class="text-gray-400 text-xs mt-0.5">Search your last name to find your PSA ID</p>
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
        <div x-show="open" x-transition class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            
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
                {{-- condition for 2 char --}}
                @if(strlen(trim($lastName)) < 2)
                    <div class="border border-dashed border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-400 text-center">
                        Results will appear here
                    </div>
                    {{-- empty result --}}

                @elseif($this->results->isEmpty())
                    <div class="border border-dashed border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-400 text-center">
                        No members found for "{{ $lastName }}"
                    </div>
                    
                @else
                {{-- retrieve the full name and psa id --}}
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
                {{-- end condition --}}

            </div>
        </div>
    </div>
</div>













