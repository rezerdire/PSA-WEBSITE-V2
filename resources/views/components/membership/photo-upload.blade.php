<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="bg-blue-50 border border-blue-100 rounded-xl p-4"
     x-data="{
         photoUrl: null,
         photoName: null,
         dragging: false,
         handleFile(file) {
             if (!file || !file.type.startsWith('image/')) return;
             this.photoName = file.name;
             const reader = new FileReader();
             reader.onload = e => this.photoUrl = e.target.result;
             reader.readAsDataURL(file);
         },
         handleDrop(e) {
             this.dragging = false;
             this.handleFile(e.dataTransfer.files[0]);
         }
     }">

    {{-- Preview --}}
    <div x-show="photoUrl" class="flex items-center gap-4">
        <img :src="photoUrl" alt="Preview" class="w-16 h-20 object-cover rounded-lg border-2 border-blue-300 shadow-sm flex-shrink-0" />
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-blue-700">Photo Ready</p>
            <p class="text-xs text-blue-500 truncate mt-0.5" x-text="photoName"></p>
            <button type="button" @click="photoUrl = null; photoName = null"
                class="mt-2 text-xs font-semibold text-slate-400 hover:text-red-500 transition-colors">
                Remove photo
            </button>
        </div>
    </div>

    {{-- Upload zone --}}
    <div x-show="!photoUrl"
         class="border-2 border-dashed rounded-lg p-4 text-center transition-colors cursor-pointer"
         :class="dragging ? 'border-blue-500 bg-blue-100' : 'border-blue-300 bg-transparent'"
         @dragover.prevent="dragging = true"
         @dragleave.prevent="dragging = false"
         @drop.prevent="handleDrop($event)"
         @click="$refs.photoInput.click()">
        <div class="flex flex-col items-center gap-2 pointer-events-none">
            <div class="w-10 h-12 border-2 border-dashed border-blue-300 rounded flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-blue-700">Upload 2×2 / Passport Photo</p>
                <p class="text-xs text-blue-500 mt-0.5">Click to browse or drag & drop here</p>
                <p class="text-xs text-slate-400 mt-1">JPG, PNG — max 5MB</p>
            </div>
        </div>
        <input x-ref="photoInput" type="file" name="photo" accept="image/*" class="sr-only"
               @change="handleFile($event.target.files[0])" />
    </div>
</div>