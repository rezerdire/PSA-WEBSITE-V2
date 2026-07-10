<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div x-show="currentStep === 'directory'" class="space-y-6">
    <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
            Member Type <span class="text-blue-600">*</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach ([
                'new_member'       => 'New Member',
                'trainee_member'   => 'Trainee Member',
                'regular_non_life' => 'Regular Non-Life',
                'regular_life'     => 'Regular Life',
            ] as $value => $label)
                <label class="relative cursor-pointer">
                    <input type="radio" name="member_type" value="{{ $value }}" class="sr-only peer" />
                    <div class="p-3 rounded-xl border-2 border-slate-200 text-center text-xs font-semibold text-slate-500 transition-all
                                peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                hover:border-slate-300">
                        {{ $label }}
                    </div>
                </label>
            @endforeach
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                Email Address <span class="text-blue-600">*</span>
            </label>
            <input type="email" name="email_address" placeholder="you@example.com"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                Mobile Number <span class="text-blue-600">*</span>
            </label>
            <input type="tel" name="mobile_number" placeholder="+63 9XX XXX XXXX"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Home Tel No.</label>
            <input type="tel" name="home_telno" placeholder="(02) XXXX XXXX"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Home Address</label>
            <input type="text" name="home_address" placeholder="Street, City, Province"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Hospital Affiliation</label>
            <input type="text" name="hospital_affiliation" placeholder="Hospital name"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Clinic Address</label>
            <input type="text" name="clinic_address" placeholder="Clinic location"
                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
        </div>
    </div>
    <div class="border-t border-slate-100 pt-5">
        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Education & Training</h3>
        <div class="space-y-4">
            @foreach ([
                ['label' => 'College of Medicine', 'name' => 'college_of_medicine',  'year' => 'college_year_graduated'],
                ['label' => 'Residency Training',  'name' => 'residency_training',   'year' => 'residency_year_graduated'],
                ['label' => 'Fellowship Training', 'name' => 'fellowship_training',  'year' => 'fellowship_year_graduated'],
            ] as $row)
                <div class="flex gap-3 items-start">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">{{ $row['label'] }}</label>
                        <input type="text" name="{{ $row['name'] }}" placeholder="{{ $row['label'] }} institution"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                    </div>
                    <div class="w-24 flex-shrink-0">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Year</label>
                        <input type="text" name="{{ $row['year'] }}" placeholder="YYYY" maxlength="4"
                            class="w-full px-3 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>