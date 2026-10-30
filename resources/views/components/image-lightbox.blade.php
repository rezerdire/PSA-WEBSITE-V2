@props([
    'src',
    'alt' => 'Image',
    'items' => [],
    'index' => 0,
])

<div
    x-data="{
        lightbox: false,
        index: {{ $index }},
        items: [],
        zoom: 1,
        panX: 0,
        panY: 0,
        dragging: false,
        startX: 0,
        startY: 0,
        get current() { return this.items[this.index] ?? null; },
        open() {
            this.lightbox = true;
            this.resetZoom();
            this.$nextTick(() => this.$refs.closeButton?.focus());
        },
        next() {
            if (this.index < this.items.length - 1) {
                this.index++;
                this.resetZoom();
            }
        },
        prev() {
            if (this.index > 0) {
                this.index--;
                this.resetZoom();
            }
        },
        resetZoom() {
            this.zoom = 1;
            this.panX = 0;
            this.panY = 0;
        },
        wheelZoom(event) {
            event.preventDefault();
            const delta = event.deltaY < 0 ? 0.2 : -0.2;
            this.zoom = Math.min(4, Math.max(1, this.zoom + delta));
            if (this.zoom === 1) {
                this.panX = 0;
                this.panY = 0;
            }
        },
        toggleZoom() {
            if (this.zoom > 1) {
                this.resetZoom();
            } else {
                this.zoom = 2;
            }
        },
        startDrag(event) {
            if (this.zoom === 1) return;
            this.dragging = true;
            const point = event.touches ? event.touches[0] : event;
            this.startX = point.clientX - this.panX;
            this.startY = point.clientY - this.panY;
        },
        onDrag(event) {
            if (!this.dragging) return;
            const point = event.touches ? event.touches[0] : event;
            this.panX = point.clientX - this.startX;
            this.panY = point.clientY - this.startY;
        },
        stopDrag() {
            this.dragging = false;
        },
    }"
    x-init="items = @js($items)"
    @keydown.escape.window="lightbox = false"
    @keydown.arrow-right.window="if (lightbox) next()"
    @keydown.arrow-left.window="if (lightbox) prev()"
    class="w-full"
>
    <button
        type="button"
        @click="open()"
        aria-label="View {{ $alt }} and zoom"
        class="group block w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-blue-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
    >
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            loading="lazy"
            decoding="async"
            class="w-full h-auto object-contain transition-transform duration-300 group-hover:scale-[1.01]"
        >
    </button>
    <p class="mt-2 text-center text-xs text-slate-500">Click the image to view and zoom</p>

    <div
        x-show="lightbox"
        x-cloak
        role="dialog"
        aria-modal="true"
        aria-label="{{ $alt }} viewer"
        class="fixed inset-0 z-[9999] flex items-center justify-center overflow-hidden bg-black/90 p-4 backdrop-blur-sm sm:p-6"
        @click="if (zoom === 1) lightbox = false"
        @wheel="wheelZoom($event)"
    >
        <button
            x-show="index > 0 && zoom === 1"
            type="button"
            @click.stop="prev()"
            aria-label="Previous image"
            class="absolute left-2 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full text-white transition hover:bg-white/10 sm:left-6"
        >
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <img
            :src="current?.src"
            :alt="current?.alt"
            :style="`transform: scale(${zoom}) translate(${panX / zoom}px, ${panY / zoom}px); cursor: ${zoom > 1 ? (dragging ? 'grabbing' : 'grab') : 'zoom-in'};`"
            class="max-h-full max-w-full select-none object-contain transition-transform duration-150"
            draggable="false"
            @click.stop="if (zoom === 1) toggleZoom()"
            @dblclick.stop="toggleZoom()"
            @mousedown.stop="startDrag($event)"
            @mousemove.window="onDrag($event)"
            @mouseup.window="stopDrag()"
            @touchstart.stop="startDrag($event)"
            @touchmove.window="onDrag($event)"
            @touchend.window="stopDrag()"
        >

        <button
            x-show="index < items.length - 1 && zoom === 1"
            type="button"
            @click.stop="next()"
            aria-label="Next image"
            class="absolute right-2 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full text-white transition hover:bg-white/10 sm:right-6"
        >
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <div class="absolute right-4 top-4 z-10 flex flex-wrap items-center justify-end gap-3 sm:right-6 sm:top-6" @click.stop>
            <button
                x-show="zoom > 1"
                type="button"
                @click.stop="zoom = Math.max(1, zoom - 0.5); if (zoom === 1) { panX = 0; panY = 0; }"
                aria-label="Zoom out"
                class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10 text-white transition hover:bg-white/20"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                </svg>
            </button>

            <button
                type="button"
                @click.stop="zoom = Math.min(4, zoom + 0.5)"
                aria-label="Zoom in"
                class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10 text-white transition hover:bg-white/20"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16M4 12h16" />
                </svg>
            </button>
            <a
                :href="current?.src"
                :download="current?.downloadName || 'image'"
                target="_blank"
                rel="noopener"
                aria-label="Download image"
                class="flex items-center gap-1.5 rounded-md bg-white/10 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-white/20"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                </svg>
                <span>Download</span>
            </a>

            <template x-if="current?.link">
                <a
                    :href="current.link"
                    x-text="current.linkLabel || 'Open related page'"
                    class="max-w-24 rounded-md bg-white/10 px-2 py-1.5 text-center text-xs font-medium text-white transition hover:bg-white/20"
                ></a>
            </template>

            <button
                type="button"
                x-ref="closeButton"
                @click="lightbox = false"
                aria-label="Close image viewer"
                class="text-3xl leading-none text-white"
            >&times;</button>
        </div>

        <div
            x-show="items.length > 1"
            class="absolute bottom-4 left-1/2 z-10 -translate-x-1/2 text-sm text-white/70"
            x-text="(index + 1) + ' / ' + items.length"
        ></div>
    </div>
</div>
