<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="space-y-3"
     x-data="{
         ids: [],
         dragging: false,
         maxFiles: 3,
         addFiles(files) {
             Array.from(files).forEach(file => {
                 if (!file.type.startsWith('image/')) return;
                 if (this.ids.length >= this.maxFiles) return;
                 const reader = new FileReader();
                 reader.onload = e => {
                     this.ids.push({ url: e.target.result, name: file.name, size: (file.size / 1024).toFixed(0) + ' KB' });
                 };
                 reader.readAsDataURL(file);
             });
         },
         remove(index) {
             this.ids.splice(index, 1);
         },
         handleDrop(e) {
             this.dragging = false;
             this.addFiles(e.dataTransfer.files);
         }
     }">
 
    {{-- Label --}}
    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">
        ID / Supporting Documents
        <span class="ml-1 font-normal normal-case text-slate-400">(Max 3 images)</span>
    </label>
 
    {{-- Uploaded previews --}}
    <div x-show="ids.length > 0" class="grid grid-cols-3 gap-3">
        <template x-for="(id, index) in ids" :key="index">
            <div class="relative group rounded-xl overflow-hidden border-2 border-slate-200 bg-slate-50 aspect-[4/3]">
                <img :src="id.url" :alt="id.name"
                     class="w-full h-full object-cover transition-opacity group-hover:opacity-60" />
                {{-- Overlay --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <button type="button" @click="remove(index)"
                        class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center shadow-md hover:bg-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                {{-- File name badge --}}
                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent px-2 pt-4 pb-1.5 pointer-events-none">
                    <p class="text-white text-[10px] font-medium truncate" x-text="id.name"></p>
                    <p class="text-white/60 text-[9px]" x-text="id.size"></p>
                </div>
            </div>
        </template>
    </div>
 
    {{-- Drop zone (hidden when max reached) --}}
    <div x-show="ids.length < maxFiles"
         class="border-2 border-dashed rounded-xl p-5 text-center transition-all cursor-pointer"
         :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-slate-300 bg-slate-50/60 hover:border-slate-400 hover:bg-slate-50'"
         @dragover.prevent="dragging = true"
         @dragleave.prevent="dragging = false"
         @drop.prevent="handleDrop($event)"
         @click="$refs.idInput.click()">
 
        <div class="flex flex-col items-center gap-2 pointer-events-none">
            <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9H5.5" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-600">
                    Upload ID / Document Scan
                </p>
                <p class="text-xs text-slate-400 mt-0.5">
                    Click to browse or drag & drop here
                </p>
            </div>
            <p class="text-[10px] text-slate-400 bg-slate-100 rounded-full px-3 py-0.5"
               x-text="`${ids.length} of ${maxFiles} uploaded`"></p>
        </div>
 
        <input x-ref="idInput" type="file" name="id_uploads[]" accept="image/*" multiple class="sr-only"
               @change="addFiles($event.target.files)" />
    </div>
 
    {{-- Max reached notice --}}
    <div x-show="ids.length >= maxFiles"
         class="flex items-center gap-2 text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Maximum of 3 ID images uploaded. Remove one to replace it.
    </div>
</div>