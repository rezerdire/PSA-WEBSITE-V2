<?php

use App\Models\Member;
use App\Models\Chapter;
use Livewire\Component;

new class extends Component {

    // Result state shown after a scan
    public ?string $scannedId    = null;
    public bool    $memberFound  = false;
    public bool    $notFound     = false;

    public string  $psaId        = '';
    public string  $firstName    = '';
    public string  $lastName     = '';
    public string  $middleName   = '';
    public string  $chapterName  = '';


    public function lookup(string $code): void
    {
        $code = trim($code);
        $this->scannedId   = $code;
        $this->memberFound = false;
        $this->notFound    = false;

        if (!preg_match('/^\d{4}$/', $code)) {
            $this->notFound = true;
            return;
        }

        $member = Member::find($code);

        if (!$member) {
            $this->notFound = true;
            return;
        }

        $chapter = Chapter::find($member->psa_chapter_code);

        $this->psaId       = $code;
        $this->firstName   = $member->mem_first_name  ?? '';
        $this->lastName    = $member->mem_last_name   ?? '';
        $this->middleName  = $member->mem_middle_name ?? '';
        $this->chapterName = $chapter->psa_chapter_desc ?? ($member->psa_chapter_code ?? '');
        $this->memberFound = true;
    }


    public function scanAgain(): void
    {
        $this->reset(['scannedId', 'memberFound', 'notFound', 'psaId', 'firstName', 'lastName', 'middleName', 'chapterName']);
        $this->dispatch('scanner-reset');
    }
};
?>

<div class="w-full max-w-md mx-auto py-12 px-4">

    <p class="font-mono text-[11px] tracking-[0.14em] uppercase text-[#ac071a] font-semibold ml-0.5 mb-1.5">
        PSA · Convention Access
    </p>
    <h1 class="font-[Space_Grotesk] text-2xl font-bold text-[#000066] tracking-tight mb-1">
        Member Scanner
    </h1>
    <p class="text-sm text-slate-500 mb-4">
        Point a member's QR code at the camera, or upload an image of one.
    </p>

    <div x-data="qrScanner()"
        x-init="init()"
        @scanner-reset.window="scanAgain()"
        wire:ignore
        x-show="!decodedText"
    >
        <!-- Mode toggle -->
        <div class="flex gap-1 mb-3 bg-slate-100 rounded-[10px] p-1">
            <button
                type="button"
                @click="mode = 'camera'; $nextTick(() => startCamera())"
                :class="mode === 'camera' ? 'bg-white text-[#000066] shadow-sm' : 'text-slate-500'"
                class="flex-1 text-[12.5px] font-semibold py-1.5 rounded-lg transition"
            >
                Camera
            </button>
            <button
                type="button"
                @click="mode = 'upload'; stopCamera()"
                :class="mode === 'upload' ? 'bg-white text-[#000066] shadow-sm' : 'text-slate-500'"
                class="flex-1 text-[12.5px] font-semibold py-1.5 rounded-lg transition"
            >
                Upload Image
            </button>
        </div>

        <!-- Camera mode -->
        <template x-if="mode === 'camera'">
            <div>
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

                <div class="bg-white border border-slate-200 rounded-[20px] p-5 shadow-[0_1px_2px_rgba(11,18,32,0.04),0_8px_24px_-12px_rgba(11,18,32,0.10)]">

                    <div class="relative aspect-square rounded-2xl overflow-hidden bg-gradient-to-br from-[#0A0E27] to-[#000066]">

                        <video
                            x-ref="video"
                            autoplay
                            muted
                            playsinline
                            class="absolute inset-0 w-full h-full object-cover"
                        ></video>

                        <canvas x-ref="canvas" class="hidden"></canvas>

                        <div
                            x-show="!decoding"
                            class="absolute left-[8%] right-[8%] top-[18%] h-0.5 bg-gradient-to-r from-transparent via-white to-transparent animate-[sweep_2.1s_ease-in-out_infinite] pointer-events-none"
                        ></div>

                        <div x-show="!decoding" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="relative w-[64%] aspect-square">
                                <div class="absolute w-9 h-9 top-0 left-0 border-t-4 border-l-4 rounded-tl-lg border-white animate-[pulseCorner_1.8s_ease-in-out_infinite]"></div>
                                <div class="absolute w-9 h-9 top-0 right-0 border-t-4 border-r-4 rounded-tr-lg border-white animate-[pulseCorner_1.8s_ease-in-out_infinite]"></div>
                                <div class="absolute w-9 h-9 bottom-0 left-0 border-b-4 border-l-4 rounded-bl-lg border-white animate-[pulseCorner_1.8s_ease-in-out_infinite]"></div>
                                <div class="absolute w-9 h-9 bottom-0 right-0 border-b-4 border-r-4 rounded-br-lg border-white animate-[pulseCorner_1.8s_ease-in-out_infinite]"></div>
                            </div>
                        </div>

                        <div x-show="decoding"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute inset-0 bg-[#000066]/85 backdrop-blur-[2px] flex flex-col items-center justify-center gap-3"
                        >
                            <div class="w-10 h-10 rounded-full border-4 border-white/30 border-t-white animate-spin"></div>
                            <p class="text-[13px] font-semibold text-white">Decoding…</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-2 mt-4 text-[13px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#000066] animate-[pulseCorner_1.4s_ease-in-out_infinite]"></span>
                        <span class="text-slate-500" x-text="decoding ? 'Decoding QR code…' : 'Scanning for QR code…'"></span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Upload mode -->
        <template x-if="mode === 'upload'">
            <div class="bg-white border border-slate-200 rounded-[20px] p-5 shadow-[0_1px_2px_rgba(11,18,32,0.04),0_8px_24px_-12px_rgba(11,18,32,0.10)]">

                <div
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="dragging = false; handleFiles($event.dataTransfer.files)"
                    @click="$refs.fileInput.click()"
                    :class="dragging ? 'border-[#000066] bg-slate-50' : 'border-slate-200'"
                    class="relative aspect-square rounded-2xl border-2 border-dashed flex flex-col items-center justify-center gap-3 cursor-pointer transition overflow-hidden bg-gradient-to-br from-slate-50 to-white"
                >
                    <template x-if="previewUrl">
                        <img :src="previewUrl" class="absolute inset-0 w-full h-full object-contain bg-white" />
                    </template>

                    <template x-if="!previewUrl">
                        <div class="flex flex-col items-center gap-2 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3" />
                            </svg>
                            <p class="text-sm text-slate-400 text-center px-6">Tap to choose an image<br>or drag one in</p>
                        </div>
                    </template>

                    <div x-show="decoding"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute inset-0 bg-white/85 backdrop-blur-[2px] flex flex-col items-center justify-center gap-3"
                    >
                        <div class="w-10 h-10 rounded-full border-4 border-slate-200 border-t-[#000066] animate-spin"></div>
                        <p class="text-[13px] font-semibold text-[#000066]">Decoding…</p>
                    </div>
                </div>

                <input
                    type="file"
                    x-ref="fileInput"
                    accept="image/*"
                    class="hidden"
                    @change="handleFiles($event.target.files)"
                />

                <p x-show="uploadError" x-text="uploadError" class="text-xs text-red-600 mt-3 text-center"></p>

                <canvas x-ref="uploadCanvas" class="hidden"></canvas>
            </div>
        </template>
    </div>


    @if ($scannedId)
        <div class="mt-4 bg-white border border-slate-200 rounded-2xl p-5">

            @if ($memberFound)
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 bg-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-[#0F9D6C] font-semibold text-sm">Member found</p>
                </div>

                <div class="divide-y divide-slate-50">
                    <div class="flex items-start gap-4 py-2">
                        <span class="text-xs text-gray-400 w-24 shrink-0 pt-0.5">PSA ID</span>
                        <span class="text-sm font-mono font-semibold text-[#000066]">{{ $psaId }}</span>
                    </div>
                    <div class="flex items-start gap-4 py-2">
                        <span class="text-xs text-gray-400 w-24 shrink-0 pt-0.5">Full Name</span>
                        <span class="text-sm font-medium text-gray-700">
                            {{ $firstName }} {{ $middleName ? $middleName . ' ' : '' }}{{ $lastName }}
                        </span>
                    </div>
                    <div class="flex items-start gap-4 py-2">
                        <span class="text-xs text-gray-400 w-24 shrink-0 pt-0.5">Chapter</span>
                        <span class="text-sm font-medium text-gray-700">{{ $chapterName }}</span>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 bg-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <p class="text-red-600 font-semibold text-sm">No matching PSA ID</p>
                </div>
                <p class="text-xs text-gray-500 font-mono">Scanned value: {{ $scannedId }}</p>
            @endif

            <button
                wire:click="scanAgain"
                class="w-full mt-5 border border-slate-200 text-[#000066] font-semibold text-[13.5px] py-2.5 rounded-[10px] hover:bg-slate-50"
            >
                Scan again
            </button>
        </div>
    @endif
</div>