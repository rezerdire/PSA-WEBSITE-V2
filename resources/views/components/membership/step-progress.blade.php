
<div class="mb-8">
    <div class="flex items-center justify-between relative">
        <div class="absolute top-4 left-0 right-0 h-0.5 bg-slate-200 z-0">
            <div class="h-full bg-blue-600 transition-all duration-500" :style="`width: ${progressPct}%`"></div>
        </div>
        <template x-for="(label, key) in steps" :key="key">
            <div class="relative z-10 flex flex-col items-center gap-2"
                 :class="canGoTo(key) ? 'cursor-pointer' : 'cursor-default'"
                 @click="canGoTo(key) && (currentStep = key)">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all duration-300"
                     :class="isDone(key) || isActive(key) ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-300 text-slate-400'">
                    <template x-if="isDone(key)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="!isDone(key)">
                        <span x-text="stepKeys.indexOf(key) + 1"></span>
                    </template>
                </div>
                <span class="text-xs font-medium hidden sm:block transition-colors"
                      :class="isActive(key) ? 'text-blue-600' : 'text-slate-400'"
                      x-text="label"></span>
            </div>
        </template>
    </div>
</div>
