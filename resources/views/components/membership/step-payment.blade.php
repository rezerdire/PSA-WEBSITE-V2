<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>


<div x-show="currentStep === 'payment'" class="space-y-6">
 
    {{-- Membership Fee Computation --}}
    <div class="rounded-xl border border-blue-100 bg-blue-50/60 overflow-hidden"
      x-data="{
            isFirstYear: null,
            residencyStartYear: null,
            get currentYear() { return new Date().getFullYear(); },
            get startYear() { return parseInt(this.residencyStartYear); },
            get isValidYear() {
                return this.residencyStartYear && this.startYear >= 1990 && this.startYear <= this.currentYear;
            },
            get oldRateYears() {
                if (!this.isValidYear || this.startYear >= 2025) return 0;
                return Math.min(2024, this.currentYear) - this.startYear + 1;
            },
            get newRateYears() {
                if (!this.isValidYear) return 0;
                const effectiveStart = Math.max(2025, this.startYear);
                if (effectiveStart > this.currentYear) return 0;
                return this.currentYear - effectiveStart + 1;
            },
            get totalYears() { return this.oldRateYears + this.newRateYears; },
            get totalBalance() {
                if (this.isFirstYear) return 3800;
                if (!this.isValidYear) return null;
                return (this.oldRateYears * 3000) + (this.newRateYears * 3800);
            }
        }">
        <div class="px-5 py-4 border-b border-blue-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-xs font-bold text-blue-800 uppercase tracking-widest">Membership Fee</h3>
        </div>
 
        <div class="px-5 py-5 space-y-4">
            {{-- First year question --}}
            <div>
                <p class="text-xs font-semibold text-slate-600 mb-3">
                    Are you a <span class="text-blue-700">first-year resident</span>?
                    <span class="text-blue-600">*</span>
                </p>
                <div class="flex gap-3">
                    <label class="relative cursor-pointer flex-1">
                        <input type="radio" name="is_first_year" value="yes" class="sr-only peer"
                               @change="isFirstYear = true; residencyStartYear = null" />
                        <div class="py-2.5 rounded-xl border-2 border-slate-200 text-center text-xs font-semibold text-slate-500 transition-all
                                    peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white
                                    hover:border-slate-300">
                            Yes, 1st Year Resident
                        </div>
                    </label>
                    <label class="relative cursor-pointer flex-1">
                        <input type="radio" name="is_first_year" value="no" class="sr-only peer"
                               @change="isFirstYear = false" />
                        <div class="py-2.5 rounded-xl border-2 border-slate-200 text-center text-xs font-semibold text-slate-500 transition-all
                                    peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                    hover:border-slate-300">
                            No, Already Started
                        </div>
                    </label>
                </div>
            </div>
 
            {{-- Residency start year (shown only when not first year) --}}
            <div x-show="isFirstYear === false" x-transition class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                        Residency Start Year <span class="text-blue-600">*</span>
                    </label>
                    <input type="number" name="residency_start_year"
                           :min="currentYear - 20" :max="currentYear"
                           placeholder="e.g. 2022"
                           x-model="residencyStartYear"
                           class="w-full sm:w-40 px-3.5 py-2.5 rounded-lg border border-slate-200 text-sm text-gray-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                    <p class="text-xs text-slate-400 mt-1">Used to compute your total membership balance.</p>
                </div>
            </div>
 
       {{-- Fee Summary --}}
        <div x-show="isFirstYear !== null" x-transition
            class="rounded-xl border border-blue-200 bg-white px-4 py-4 space-y-2">

            {{-- First-year: simple flat rate --}}
            <template x-if="isFirstYear === true">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Annual Fee (2025 rate)</span>
                    <span class="font-semibold text-slate-700">₱3,800.00</span>
                </div>
            </template>

            {{-- Multi-year: show rate breakdown --}}
            <template x-if="isFirstYear === false && isValidYear">
                <div class="space-y-2">
                    <template x-if="oldRateYears > 0">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">
                                Before 2025
                                <span class="text-slate-400" x-text="`(${oldRateYears} yr${oldRateYears > 1 ? 's' : ''} × ₱3,000)`"></span>
                            </span>
                            <span class="font-semibold text-slate-700"
                                x-text="`₱${(oldRateYears * 3000).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`"></span>
                        </div>
                    </template>
                    <template x-if="newRateYears > 0">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">
                                2025 onwards
                                <span class="text-slate-400" x-text="`(${newRateYears} yr${newRateYears > 1 ? 's' : ''} × ₱3,800)`"></span>
                            </span>
                            <span class="font-semibold text-slate-700"
                                x-text="`₱${(newRateYears * 3800).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`"></span>
                        </div>
                    </template>
                </div>
            </template>

            <div class="border-t border-slate-100 pt-2 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Total Balance</span>
                <template x-if="totalBalance !== null">
                    <span class="text-base font-bold text-blue-700"
                        x-text="`₱${totalBalance.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`"></span>
                </template>
                <template x-if="totalBalance === null && isFirstYear === false">
                    <span class="text-xs text-slate-400 italic">Enter start year above</span>
                </template>
            </div>
        </div>
    </div>
</div>


    {{-- Payment Methods --}}
    <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest">Payment Methods</h3>
        </div>
 
        <div class="divide-y divide-slate-100">
 
            {{-- PSA Office --}}
            <div class="px-5 py-4 flex gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-700">Pay at the PSA Office</p>
                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                        Room 102, Philippine Medical Association Building<br>
                        North Avenue, Quezon City
                    </p>
                </div>
            </div>
 
            {{-- BPI --}}
            <div class="px-5 py-4 flex gap-3">
                <img src = "https://sa.kapamilya.com/absnews/abscbnnews/media/2019/news/08/01/bpi.jpg"  class ="h-12 w-20 object-cover " >
                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-slate-700">BPI — SM North Branch</p>
                    <p class="text-xs text-slate-500">
                        Account Name: <span class="font-medium text-slate-700">Philippine Society of Anesthesiologists, Inc.</span>
                    </p>
                    <p class="text-xs text-slate-500">
                        Account No.: <span class="font-mono font-semibold text-slate-700 tracking-wide">004433 1136 03</span>
                    </p>
                </div>
            </div>
 
            {{-- Landbank --}}
            <div class="px-5 py-4 flex gap-3">
                    <img src = "https://cdn.prod.website-files.com/640ea4106aa3032db2a6cefb/6489573b510c7c1e88391a48_64547d9d31f9345f598dddb3_Land%2520Bank%2520of%2520the%2520Philippines%2520-%2520Balanga%2520Branch.png"  class ="h-12 w-20 object-fit " >
                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-slate-700">Landbank of the Philippines — North Avenue Branch</p>
                    <p class="text-xs text-slate-500">
                        Account Name: <span class="font-medium text-slate-700">Philippine Society of Anesthesiologists, Inc.</span>
                    </p>
                    <p class="text-xs text-slate-500">
                        Account No.: <span class="font-mono font-semibold text-slate-700 tracking-wide">0711-0635-44</span>
                    </p>
                </div>
            </div>
 
            {{-- GCash --}}
            <div class="px-5 py-4 flex gap-3">
                    <img src = "https://1000logos.net/wp-content/uploads/2023/05/GCash-Logo.png"  class ="h-12 w-20 object-cover " >
                <div class="space-y-0.5">
                    <p class="text-xs font-semibold text-slate-700">GCash — Transfer to BPI</p>
                    <p class="text-xs text-slate-500">
                        Account No.: <span class="font-mono font-semibold text-slate-700 tracking-wide">004433 1136 03</span>
                        <span class="text-slate-400"> or </span>
                        <span class="font-mono font-semibold text-slate-700 tracking-wide">4433 1136 03</span>
                    </p>
                    <p class="text-xs text-slate-500">
                        Account Name: <span class="font-medium text-slate-700">PSA, Inc.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
 
   
    {{-- Proof of Payment Upload --}}
    <div class="space-y-3"
         x-data="{
             proofs: [],
             dragging: false,
             maxFiles: 3,
             addFiles(files) {
                 Array.from(files).forEach(file => {
                     if (!file.type.startsWith('image/')) return;
                     if (this.proofs.length >= this.maxFiles) return;
                     const reader = new FileReader();
                     reader.onload = e => {
                         this.proofs.push({ url: e.target.result, name: file.name, size: (file.size / 1024).toFixed(0) + ' KB' });
                     };
                     reader.readAsDataURL(file);
                 });
             },
             remove(index) { this.proofs.splice(index, 1); },
             handleDrop(e) { this.dragging = false; this.addFiles(e.dataTransfer.files); }
         }">
 
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">
            Proof of Payment
            <span class="ml-1 font-normal normal-case text-slate-400">Maximum of 3 Photos</span>
        </label>
 
        {{-- Previews --}}
        <div x-show="proofs.length > 0" class="grid grid-cols-3 gap-3">
            <template x-for="(proof, index) in proofs" :key="index">
                <div class="relative group rounded-xl overflow-hidden border-2 border-slate-200 bg-slate-50 aspect-[4/3]">
                    <img :src="proof.url" :alt="proof.name"
                         class="w-full h-full object-cover transition-opacity group-hover:opacity-60" />
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="button" @click="remove(index)"
                            class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center shadow-md hover:bg-red-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent px-2 pt-4 pb-1.5 pointer-events-none">
                        <p class="text-white text-[10px] font-medium truncate" x-text="proof.name"></p>
                        <p class="text-white/60 text-[9px]" x-text="proof.size"></p>
                    </div>
                </div>
            </template>
        </div>
 
        {{-- Drop Zone --}}
        <div x-show="proofs.length < maxFiles"
             class="border-2 border-dashed rounded-xl p-5 text-center transition-all cursor-pointer"
             :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-slate-300 bg-slate-50/60 hover:border-slate-400 hover:bg-slate-50'"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="handleDrop($event)"
             @click="$refs.proofInput.click()">
 
            <div class="flex flex-col items-center gap-2 pointer-events-none">
                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-600">Upload Proof of Payment</p>
                    <p class="text-xs text-slate-400 mt-0.5">Click to browse or drag & drop here</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">JPG, PNG — max 5MB each</p>
                </div>
                <p class="text-[10px] text-slate-400 bg-slate-100 rounded-full px-3 py-0.5"
                   x-text="`${proofs.length} of ${maxFiles} uploaded`"></p>
            </div>
 
            <input x-ref="proofInput" type="file" name="payment_proofs[]" accept="image/*" multiple class="sr-only"
                   @change="addFiles($event.target.files)" />
        </div>
 
        {{-- Max reached --}}
        <div x-show="proofs.length >= maxFiles"
             class="flex items-center gap-2 text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Maximum of 3 proofs uploaded. Remove one to replace it.
        </div>
    </div>
</div>