<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
@section('title', 'Event Registration - PSA')
@extends('layouts.app')
@section('content')
 

<div
    x-data="qrScanner()"
    x-init="init()"
    class="w-full max-w-md mx-auto py-12 px-4"
>
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

        {{-- manual fallback --}}
        <div class="flex gap-2 mt-4">
            <input
                type="text"
                x-model="manualInput"
                @keydown.enter="submitManual()"
                placeholder="or type Member ID manually"
                class="flex-1 border border-slate-200 rounded-[10px] px-3 py-2.5 font-mono text-[13px] outline-none focus:border-[#000066]"
            >
            <button
                @click="submitManual()"
                class="bg-[#000066] text-white font-semibold text-[13px] px-4 rounded-[10px]"
            >
                Find
            </button>
        </div>
    </div>

    {{-- decoded output preview --}}
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

<style>
    @keyframes sweep {
        0%, 100% { top: 18%; opacity: 0.9; }
        50% { top: 82%; opacity: 0.5; }
    }
    @keyframes pulseCorner {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.35; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
    function qrScanner() {
        return {
            cameras: [],
            selectedCameraId: null,
            isMobile: /Android|iPhone|iPad|iPod/i.test(navigator.userAgent),
            decodedText: null,
            manualInput: '',
            stream: null,
            scanLoopId: null,

            async init() {
                await this.loadCameras();
                if (this.selectedCameraId) await this.startCamera();
            },

            async loadCameras() {
                try {
                    // Request permission once so device labels are populated
                    // (without this, labels come back blank on most browsers).
                    const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
                    tempStream.getTracks().forEach(t => t.stop());

                    const devices = await navigator.mediaDevices.enumerateDevices();
                    this.cameras = devices
                        .filter(d => d.kind === 'videoinput')
                        .map((d, i) => ({
                            deviceId: d.deviceId,
                            label: d.label || `Camera ${i + 1}`,
                        }));

                    if (this.cameras.length === 0) return;

                    const back = this.cameras.find(c => /back|rear|environment/i.test(c.label));
                    this.selectedCameraId = (back || this.cameras[0]).deviceId;
                } catch (err) {
                    console.error('Could not access camera list:', err);
                }
            },

            async loadCameras() {
                try {
                    // Request permission once so device labels are populated.
                    const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
                    tempStream.getTracks().forEach(t => t.stop());

                    const devices = await navigator.mediaDevices.enumerateDevices();
                    this.cameras = devices
                        .filter(d => d.kind === 'videoinput')
                        .map((d, i) => ({
                            deviceId: d.deviceId,
                            label: d.label || `Camera ${i + 1}`,
                        }));

                    if (this.cameras.length === 0) return;

                    const back = this.cameras.find(c => /back|rear|environment/i.test(c.label));
                    this.selectedCameraId = (back || this.cameras[0]).deviceId;
                } catch (err) {
                    console.error('Could not access camera list:', err);
                }
            },

            async startCamera() {
                this.stopCamera();

                const videoConstraints = {
                    deviceId: this.selectedCameraId ? { exact: this.selectedCameraId } : undefined,
                };

                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints });
                    this.$refs.video.srcObject = this.stream;
                    await this.$refs.video.play();
                    this.scanLoop();
                } catch (err) {
                    console.error('Camera start failed:', err);
                }
            },

            stopCamera() {
                if (this.scanLoopId) cancelAnimationFrame(this.scanLoopId);
                if (this.stream) {
                    this.stream.getTracks().forEach(t => t.stop());
                    this.stream = null;
                }
            },

            switchCamera() {
                this.startCamera();
            },

            scanLoop() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });

                // Decode at ~8 scans/sec instead of every animation frame
                // (~60/sec) — jsQR is fairly heavy per call, and QR codes
                // don't need 60fps to be caught reliably.
                const scanIntervalMs = 125;
                let lastScanTime = 0;

                // Downscale before decoding — jsQR doesn't need full camera
                // resolution (e.g. 1920x1080) to read a code, and smaller
                // images decode dramatically faster.
                const maxDecodeWidth = 480;

                const tick = (timestamp) => {
                    if (this.decodedText) return; // stop scanning once matched

                    if (
                        video.readyState === video.HAVE_ENOUGH_DATA &&
                        timestamp - lastScanTime >= scanIntervalMs
                    ) {
                        lastScanTime = timestamp;

                        const scale = Math.min(1, maxDecodeWidth / video.videoWidth);
                        const w = Math.round(video.videoWidth * scale);
                        const h = Math.round(video.videoHeight * scale);

                        if (canvas.width !== w || canvas.height !== h) {
                            canvas.width = w;
                            canvas.height = h;
                        }

                        ctx.drawImage(video, 0, 0, w, h);
                        const imageData = ctx.getImageData(0, 0, w, h);
                        const code = jsQR(imageData.data, imageData.width, imageData.height, {
                            inversionAttempts: 'dontInvert', // skip extra inverted-color pass, faster
                        });

                        if (code && code.data) {
                            this.decodedText = code.data;
                            this.stopCamera();
                            return;
                        }
                    }
                    this.scanLoopId = requestAnimationFrame(tick);
                };
                this.scanLoopId = requestAnimationFrame(tick);
            },

            submitManual() {
                const val = this.manualInput.trim();
                if (!val) return;
                this.decodedText = val;
                this.stopCamera();
            },

            scanAgain() {
                this.decodedText = null;
                this.manualInput = '';
                this.startCamera();
            },
        };
    }
</script>