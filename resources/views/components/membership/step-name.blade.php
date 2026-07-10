<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div x-show="currentStep === 'name'" class="space-y-6">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 sm:col-span-4">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                First Name <span class="text-blue-600">*</span>
            </label>
            <input type="text" name="firstname" placeholder="e.g. Christian"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>  
        <div class="col-span-12 sm:col-span-5">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                Last Name <span class="text-blue-600">*</span>
            </label>
            <input type="text" name="lastname" placeholder="e.g. Vacaro"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div class="col-span-12 sm:col-span-1">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">M.I.</label>
            <input type="text" name="middle_initial" maxlength="2" placeholder="B."
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div class="col-span-12 sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Suffix</label>
            <input type="text" name="suffix" placeholder="Jr., Sr."
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
    </div>

    <x-membership.photo-upload />
</div>