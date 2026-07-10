<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div x-show="currentStep === 'benefits'" class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Birth Date</label>
            <input type="date" name="birth_date"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Name of Spouse</label>
            <input type="text" name="name_of_spouse" placeholder="Full name of spouse"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
            Name of Dependents / Beneficiaries
            <span class="ml-1 font-normal normal-case text-slate-400">(DBF, LAF)</span>
        </label>
        <div class="space-y-3">
            @foreach (['1st', '2nd', '3rd', '4th'] as $n)
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-xs font-bold text-blue-600 flex-shrink-0">
                        {{ $loop->iteration }}
                    </span>
                    <input type="text" name="beneficiary_{{ $loop->iteration }}"
                        placeholder="{{ $n }} beneficiary / dependent name"
                        class="flex-1 px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                </div>
            @endforeach
        </div>
    </div>
</div>