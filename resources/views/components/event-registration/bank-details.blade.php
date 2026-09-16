<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
{{-- GAGAWIN KONG REUSABLE COMPONENT IN THE FUTURE --}}
<div class="rounded-xl border border-red-300 bg-red-50 px-5 py-4 mb-3">

    <div class="flex items-center gap-2 mb-3">
        <span class="text-xs font-bold uppercase tracking-wide" style="color: #cc0000;">Bank Details</span>
        <span class="h-px flex-1" style="background-color: #f3c2c2;"></span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
        <div>
            <p class="text-[10px] text-red-400 font-medium uppercase tracking-wide mb-1">Bank</p>
            <img src="{{ asset('bpilogo.png') }}" alt="BPI" class="w-10 h-10 object-contain">
        </div>
        <div>
            <p class="text-[10px] text-red-400 font-medium uppercase tracking-wide">Account Number</p>
            <p class="font-mono font-semibold text-gray-800">4433-1136-03</p>
        </div>
        <div>
            <p class="text-[10px] text-red-400 font-medium uppercase tracking-wide">Account Name</p>
            <p class="font-semibold text-gray-800">Philippine Society of Anesthesiologists, Inc.</p>
        </div>
    </div>
</div>