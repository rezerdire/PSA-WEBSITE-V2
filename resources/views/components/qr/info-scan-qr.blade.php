<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
{{-- jsQR is a plain global-attaching library, safe to load as a normal script tag --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

{{-- app.js (bundled via Vite) now imports qr-script.js internally and
     attaches window.qrScanner — no separate <script src="qr-script.js">
     tag needed here anymore. --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

@section('title', 'Event Registration - PSA')
@extends('layouts.app')
@section('content')

{{-- wire:ignore tells Livewire's morph step to leave this whole subtree
     alone on re-render, so Alpine's component state (decodedText, cameras,
     etc.) never gets wiped out from under it. --}}
<div x-data="qrScanner()"
    x-init="init()" wire:ignore class="w-full max-w-md mx-auto py-12 px-4">
    <p class="font-mono text-[11px] tracking-[0.14em] uppercase text-[#ac071a] font-semibold ml-0.5 mb-1.5">
        PSA · Convention Access
    </p>
    <h1 class="font-[Space_Grotesk] text-2xl font-bold text-[#000066] tracking-tight mb-1">
        Member Scanner
    </h1>
    <p class="text-sm text-slate-500 mb-4">
        Point a member's QR code at the camera to scan it.
    </p>

    {{-- Camera picker: single dropdown listing every camera the browser can see --}}
    <div class="flex items-center gap-2 mb-3">
        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap">
            Camera
        </label>
        <select
            x-model="selectedCameraId"
            @change="switchCamera()"
            :disabled="cameras.length === 0"
            class="flex-1 border border-slate-200 rounded-[10px] px-3 py-2 text-[13px] text-[#000066] bg-white outline-none focus:border-[#000066] disabled:text-slate-400"
        >
            <template x-if="cameras.length === 0">
                <option value="">Loading cameras…</option>
            </template>
            <template x-for="cam in cameras" :key="cam.deviceId">
                <option :value="cam.deviceId" x-text="cam.label"></option>
            </template>
        </select>
    </div>

    {{-- Scanner card --}}
    <div class="bg-white border border-slate-200 rounded-[20px] p-5 shadow-[0_1px_2px_rgba(11,18,32,0.04),0_8px_24px_-12px_rgba(11,18,32,0.10)]">

        <div class="relative aspect-square rounded-2xl overflow-hidden bg-gradient-to-br from-[#0A0E27] to-[#000066]">

            {{-- our own video element, fully under our control --}}
            <video
                x-ref="video"
                autoplay
                muted
                playsinline
                class="absolute inset-0 w-full h-full object-cover"
            ></video>

            {{-- hidden canvas used only for frame decoding --}}
            <canvas x-ref="canvas" class="hidden"></canvas>

            {{-- sweep line, hidden once a code is found --}}
            <div
                x-show="!decodedText"
                class="absolute left-[8%] right-[8%] top-[18%] h-0.5 bg-gradient-to-r from-transparent via-white to-transparent animate-[sweep_2.1s_ease-in-out_infinite] pointer-events-none"
            ></div>

            {{-- corner frame --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="relative w-[64%] aspect-square">
                    <div
                        class="absolute w-9 h-9 top-0 left-0 border-t-4 border-l-4 rounded-tl-lg transition-colors duration-300"
                        :class="decodedText ? 'border-[#0F9D6C] shadow-[0_0_14px_1px_rgba(15,157,108,0.55)]' : 'border-white animate-[pulseCorner_1.8s_ease-in-out_infinite]'"
                    ></div>
                    <div
                        class="absolute w-9 h-9 top-0 right-0 border-t-4 border-r-4 rounded-tr-lg transition-colors duration-300"
                        :class="decodedText ? 'border-[#0F9D6C] shadow-[0_0_14px_1px_rgba(15,157,108,0.55)]' : 'border-white animate-[pulseCorner_1.8s_ease-in-out_infinite]'"
                    ></div>
                    <div
                        class="absolute w-9 h-9 bottom-0 left-0 border-b-4 border-l-4 rounded-bl-lg transition-colors duration-300"
                        :class="decodedText ? 'border-[#0F9D6C] shadow-[0_0_14px_1px_rgba(15,157,108,0.55)]' : 'border-white animate-[pulseCorner_1.8s_ease-in-out_infinite]'"
                    ></div>
                    <div
                        class="absolute w-9 h-9 bottom-0 right-0 border-b-4 border-r-4 rounded-br-lg transition-colors duration-300"
                        :class="decodedText ? 'border-[#0F9D6C] shadow-[0_0_14px_1px_rgba(15,157,108,0.55)]' : 'border-white animate-[pulseCorner_1.8s_ease-in-out_infinite]'"
                    ></div>
                </div>
            </div>
        </div>

        {{-- status row --}}
        <div class="flex items-center justify-center gap-2 mt-4 text-[13px]">
            <span
                class="w-1.5 h-1.5 rounded-full transition-colors"
                :class="decodedText ? 'bg-[#0F9D6C]' : 'bg-[#000066] animate-[pulseCorner_1.4s_ease-in-out_infinite]'"
            ></span>
            <span :class="decodedText ? 'text-[#0F9D6C] font-semibold' : 'text-slate-500'" x-text="decodedText ? 'QR code scanned' : 'Scanning for QR code…'"></span>
        </div>


    </div>

    <div x-show="decodedText" x-cloak class="mt-4 bg-white border border-slate-200 rounded-2xl p-5">
        <p class="text-[11px] uppercase tracking-wide text-slate-400 font-semibold mb-1">Decoded value</p>
        <p class="font-mono text-sm text-[#000066] break-all" x-text="decodedText"></p>
        <button
            @click="scanAgain()"
            class="w-full mt-4 border border-slate-200 text-[#000066] font-semibold text-[13.5px] py-2.5 rounded-[10px] hover:bg-slate-50"
        >
            Scan again
        </button>
    </div>
</div>
