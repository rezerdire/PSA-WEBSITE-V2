<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

@props([
    'name',
    'wireModel'  => null,   {{-- e.g. "payment_proof" --}}
    'label'      => 'Upload Image',
    'accept'     => '.jpg,.jpeg,.png,.heic',
    'required'   => false,
    'color'      => '#ac071a',
])

<div
    x-data="{
        preview: null,
        fileName: null,
        dragging: false,
        handleFile(file) {
            if (!file) return;
            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = e => this.preview = e.target.result;
            reader.readAsDataURL(file);
        },
        handleInput(e) {
            const file = e.target.files[0];
            this.handleFile(file);

            {{-- Sync to Livewire's hidden input if wireModel is set --}}
            @if ($wireModel)
                const dt = new DataTransfer();
                if (file) dt.items.add(file);
                this.$refs.livewireInput.files = dt.files;
                this.$refs.livewireInput.dispatchEvent(new Event('change'));
            @endif
        },
        handleDrop(e) {
            this.dragging = false;
            const file = e.dataTransfer.files[0];
            if (file) {
                this.$refs.alpineInput.files = e.dataTransfer.files;
                this.handleFile(file);

                @if ($wireModel)
                    this.$refs.livewireInput.files = e.dataTransfer.files;
                    this.$refs.livewireInput.dispatchEvent(new Event('change'));
                @endif
            }
        },
        clear() {
            this.preview = null;
            this.fileName = null;
            this.$refs.alpineInput.value = '';
            @if ($wireModel)
                this.$refs.livewireInput.value = '';
                this.$refs.livewireInput.dispatchEvent(new Event('change'));
            @endif
        }
    }"
    @dragover.prevent="dragging = true"
    @dragleave.prevent="dragging = false"
    @drop.prevent="handleDrop($event)"
>
    <label class="block text-xs font-medium text-gray-500 mb-1.5">
        {{ $label }}
        @if ($required) <span class="text-red-400">*</span> @endif
    </label>

    {{-- wire:ignore keeps Livewire from morphing the visual zone --}}
    <div wire:ignore>

        {{-- Drop Zone --}}
        <div
            x-show="!preview"
            :class="dragging ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
            class="rounded-xl border-2 border-dashed p-6 text-center transition cursor-pointer"
            @click="$refs.alpineInput.click()">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
            </svg>
            <p class="text-xs text-gray-400 mb-1">Drag & drop or click to browse</p>
            <p class="text-xs text-gray-300">JPG, JPEG, PNG, HEIC accepted</p>
        </div>

        {{-- Preview --}}
        <div x-show="preview" x-transition class="rounded-xl overflow-hidden border border-gray-100 bg-gray-50">
            <img :src="preview" :alt="fileName" class="w-full max-h-52 object-contain p-2">
            <div class="flex items-center justify-between gap-2 px-4 py-2.5 border-t border-gray-100 bg-white">
                <div class="flex items-center gap-2 min-w-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 text-gray-400" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                        <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                    </svg>
                    <span x-text="fileName" class="text-xs text-gray-500 truncate"></span>
                </div>
                <button type="button" @click.stop="clear()"
                    class="shrink-0 text-xs font-semibold text-red-500 hover:text-red-700 transition">
                    Remove
                </button>
            </div>
        </div>

        {{-- Alpine's visible input (for preview only) --}}
        <input
            x-ref="alpineInput"
            type="file"
            accept="{{ $accept }}"
            @change="handleInput($event)"
            class="hidden">

    </div>{{-- end wire:ignore --}}

    {{-- Livewire's input lives OUTSIDE wire:ignore so it survives re-renders --}}
    @if ($wireModel)
        <input
            x-ref="livewireInput"
            type="file"
            name="{{ $name }}"
            wire:model="{{ $wireModel }}"
            accept="{{ $accept }}"
            {{ $required ? 'required' : '' }}
            class="hidden">
    @else
        <input
            x-ref="livewireInput"
            type="file"
            name="{{ $name }}"
            accept="{{ $accept }}"
            {{ $required ? 'required' : '' }}
            class="hidden">
    @endif

</div>