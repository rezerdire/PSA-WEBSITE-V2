<?php

use App\Models\GalleryDay;
use App\Models\GalleryEvent;
use App\Models\GalleryImage;
use Livewire\Component;

new class extends Component
{
    public GalleryEvent $event;
    public GalleryDay $day;
    public ?string $categorySlug = null;
    public int $perPage = 24;

    public function mount(GalleryEvent $event, GalleryDay $day): void
    {
        $this->event = $event;
        $this->day = $day;
    }

    public function selectCategory(?string $slug): void
    {
        $this->categorySlug = $slug;
        $this->perPage = 24;
    }

    public function loadMore(): void
    {
        $this->perPage += 24;
    }

    public function render()
    {
        $categories = $this->day->categories;

        $imagesQuery = GalleryImage::query()
            ->whereIn('gallery_category_id', $categories->pluck('id'))
            ->when($this->categorySlug, function ($q) use ($categories) {
                $catId = $categories->firstWhere('slug', $this->categorySlug)?->id;
                $q->where('gallery_category_id', $catId);
            })
            ->orderBy('order');

        return view('components.gallery.day-page', [
            'categories' => $categories,
            'images' => $imagesQuery->limit($this->perPage)->get(),
            'hasMore' => $imagesQuery->count() > $this->perPage,
        ]);
    }
};
?>

<div
    x-data="{
        lightbox: false,
        index: 0,
        items: [],
        zoom: 1,
        panX: 0,
        panY: 0,
        dragging: false,
        startX: 0,
        startY: 0,
        get current() { return this.items[this.index] ?? null; },
        open(i) { this.index = i; this.lightbox = true; this.resetZoom(); },
        next() { if (this.index < this.items.length - 1) { this.index++; this.resetZoom(); } },
        prev() { if (this.index > 0) { this.index--; this.resetZoom(); } },
        resetZoom() { this.zoom = 1; this.panX = 0; this.panY = 0; },
        wheelZoom(e) {
            e.preventDefault();
            const delta = e.deltaY < 0 ? 0.2 : -0.2;
            this.zoom = Math.min(4, Math.max(1, this.zoom + delta));
            if (this.zoom === 1) { this.panX = 0; this.panY = 0; }
        },
        toggleZoom() {
            if (this.zoom > 1) { this.resetZoom(); }
            else { this.zoom = 2; }
        },
        startDrag(e) {
            if (this.zoom === 1) return;
            this.dragging = true;
            const p = e.touches ? e.touches[0] : e;
            this.startX = p.clientX - this.panX;
            this.startY = p.clientY - this.panY;
        },
        onDrag(e) {
            if (!this.dragging) return;
            const p = e.touches ? e.touches[0] : e;
            this.panX = p.clientX - this.startX;
            this.panY = p.clientY - this.startY;
        },
        stopDrag() { this.dragging = false; },
    }"
    x-init="items = @js($images->map(fn ($image) => [
        'thumb' => Storage::disk('public')->url($image->thumb_path),
        'large' => Storage::disk('public')->url($image->large_path),
        'alt' => $image->category->name,
    ]))"
    @keydown.escape.window="lightbox = false"
    @keydown.arrow-right.window="if (lightbox) next()"
    @keydown.arrow-left.window="if (lightbox) prev()"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-12"
>

    {{-- Header --}}
    <div class="mb-8 pb-6 border-b border-slate-200">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1.5">
            Gallery
        </p>

        <div class="flex flex-wrap items-start justify-between gap-4">
            {{-- Title --}}
            <div class="flex items-center gap-3">
                <span class="w-1 h-8 bg-blue-600 rounded-full"></span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                        {{ $event->name }}
                    </h1>
                    <p class="text-sm text-slate-500">{{ $day->label }}</p>
                </div>
            </div>

            {{-- Day tab strip --}}
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($event->days as $d)
                    @if ($d->id === $day->id)
                        <span class="px-4 py-2 rounded-full text-sm font-semibold bg-blue-600 text-white">
                            {{ $d->label }}
                        </span>
                    @else
                        <a
                            href="{{ route('gallery.day', [$event, $d]) }}"
                            wire:navigate
                            class="px-4 py-2 rounded-full text-sm font-medium border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            {{ $d->label }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Category filter --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <button
            wire:click="selectCategory(null)"
            class="px-4 py-2 rounded-full text-sm border {{ !$categorySlug ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }} transition-colors"
        >All</button>

        @foreach ($categories as $category)
            <button
                wire:click="selectCategory('{{ $category->slug }}')"
                class="px-4 py-2 rounded-full text-sm border {{ $categorySlug === $category->slug ? 'bg-blue-600 text-white border-blue-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }} transition-colors"
            >{{ $category->name }}</button>
        @endforeach
    </div>

    {{-- Image grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" wire:key="grid-{{ $categorySlug }}">
        @foreach ($images as $i => $image)
            <button
                type="button"
                @click="open({{ $i }})"
                class="block w-full aspect-square overflow-hidden rounded-lg border border-slate-200"
            >
                <img
                    src="{{ Storage::disk('public')->url($image->thumb_path) }}"
                    loading="lazy"
                    decoding="async"
                    width="{{ $image->width }}"
                    height="{{ $image->height }}"
                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                    alt="{{ $image->category->name }}"
                >
            </button>
        @endforeach
    </div>

    {{-- Load more sentinel --}}
    @if ($hasMore)
        <div
            x-intersect.margin.200px="$wire.loadMore()"
            wire:loading.class="opacity-50"
            class="text-center py-6 text-sm text-slate-500"
        >
            Loading more photos...
        </div>
    @endif

    {{-- Lightbox --}}
    <div
        x-show="lightbox"
        x-cloak
        class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4 overflow-hidden"
        @click="if (zoom === 1) lightbox = false"
        @wheel="wheelZoom($event)"
    >
        {{-- Prev --}}
        <button
            x-show="index > 0 && zoom === 1"
            @click.stop="prev()"
            class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full text-white hover:bg-white/10 transition-colors z-10"
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        {{-- Image --}}
        <img
            :src="current?.large"
            :alt="current?.alt"
            :style="`transform: scale(${zoom}) translate(${panX / zoom}px, ${panY / zoom}px); cursor: ${zoom > 1 ? (dragging ? 'grabbing' : 'grab') : 'zoom-in'};`"
            class="max-h-full max-w-full object-contain transition-transform duration-150 select-none"
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

        {{-- Next --}}
        <button
            x-show="index < items.length - 1 && zoom === 1"
            @click.stop="next()"
            class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-full text-white hover:bg-white/10 transition-colors z-10">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        {{-- Top-right controls --}}
        <div class="absolute top-4 right-4 flex items-center gap-3 z-10" @click.stop>
            {{-- Zoom out --}}
            <button
                x-show="zoom > 1"
                @click.stop="zoom = Math.max(1, zoom - 0.5); if (zoom === 1) { panX = 0; panY = 0; }"
                class="text-white w-8 h-8 flex items-center justify-center rounded-md bg-white/10 hover:bg-white/20 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                </svg>
            </button>

            {{-- Zoom in --}}
            <button
                @click.stop="zoom = Math.min(4, zoom + 0.5)"
                class="text-white w-8 h-8 flex items-center justify-center rounded-md bg-white/10 hover:bg-white/20 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16M4 12h16"/>
                </svg>
            </button>

            {{-- Download --}}
            <a
                :href="current?.large"
                :download="current ? current.alt + '.jpg' : ''"
                target="_blank"
                class="text-white text-sm font-medium px-3 py-1.5 rounded-md bg-white/10 hover:bg-white/20 transition-colors flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
                Download
            </a>

            {{-- Close --}}
            <button @click="lightbox = false" class="text-white text-3xl leading-none">&times;</button>
        </div>

        {{-- Counter --}}
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/70 text-sm z-10" x-text="(index + 1) + ' / ' + items.length"></div>
    </div>

</div>