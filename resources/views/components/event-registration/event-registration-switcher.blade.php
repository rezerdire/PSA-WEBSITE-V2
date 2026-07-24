<?php

    use Livewire\Component;

    new class extends Component {

        public string $mode = 'member'; // 'member' | 'guest'

        public function switchTo(string $mode): void
        {
            if (in_array($mode, ['member', 'guest'], true)) {
                $this->mode = $mode;
            }
        }
    };
    ?>
    <div>
        {{-- Switch Button --}}
<div class="mb-8 flex flex-col items-center gap-3">
    
    <div class="text-center">
        <h2 class="font-bold text-xl text-[#000066]">Are you a Member of PSA?</h2>
        <p class="text-gray-400 text-xs mt-0.5">Select which registration applies to you.</p>
    </div>
    <div class="inline-flex rounded-xl border border-gray-200 p-1 bg-gray-50 gap-1">
        <button type="button" wire:click="switchTo('member')"
            class="px-6 py-2 rounded-lg text-sm font-semibold transition
            {{ $mode === 'member' ? 'bg-[#000066] text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            PSA Member
        </button>
        <button type="button" wire:click="switchTo('guest')"
            class="px-6 py-2 rounded-lg text-sm font-semibold transition
            {{ $mode === 'guest' ? 'bg-[#000066] text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Non-Member / Guest
        </button>
    </div>
</div>

        {{-- Conditional Form --}}
    {{-- Conditional Form --}}
@if ($mode === 'member')
        <livewire:event-registration.psa-checker :key="'psa-checker-' . $mode" />

    <x-event-registration.form-layout>
        <livewire:event-registration.event-reg-form :key="'event-reg-form-' . $mode" />
    </x-event-registration.form-layout>
@else
    <x-event-registration.form-layout>
        <livewire:event-registration.event-registration-guest :key="'event-reg-form-guest-' . $mode" />
    </x-event-registration.form-layout>
@endif

</div>