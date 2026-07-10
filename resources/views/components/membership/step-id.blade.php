<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div x-show="currentStep === 'id'" class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">PRC No.</label>
            <div class="flex gap-2">
                <input type="text" name="prc_no" placeholder="PRC number"
                    class="flex-1 px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                <input type="date" name="prc_exp_date"
                    class="w-36 px-3 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
            </div>
            <p class="text-xs text-slate-400 mt-1">Number & Expiry Date</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">PMA No.</label>
            <div class="flex gap-2">
                <input type="text" name="pma_no" placeholder="PMA number"
                    class="flex-1 px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                <input type="date" name="pma_exp_date"
                    class="w-36 px-3 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
            </div>
            <p class="text-xs text-slate-400 mt-1">Number & Expiry Date</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">PHIC No.</label>
            <input type="text" name="phic_no" placeholder="PhilHealth number"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Diplomate No.</label>
            <div class="flex gap-2">
                <input type="text" name="diplomate_no" placeholder="Diplomate number"
                    class="flex-1 px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                <input type="text" name="diplo_year" placeholder="Year" maxlength="4"
                    class="w-20 px-3 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
            </div>
            <p class="text-xs text-slate-400 mt-1">Number & Diplomate Year</p>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Fellow No.</label>
            <input type="text" name="fellow_no" placeholder="Fellow number"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">PSA Regional Chapter</label>
            <input type="text" name="psa_regional_chapter" placeholder="e.g. NCR, Region IV-A"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">PMA Local Component Society</label>
            <input type="text" name="pma_local_component_society" placeholder="e.g. Manila Medical Society"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
    </div>
    <x-membership.id-upload />
</div>