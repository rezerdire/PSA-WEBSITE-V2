@php
    $slides = \App\Models\HeroSlide::query()
        ->published()
        ->get();
@endphp

@if ($slides->isNotEmpty())
    <section
        x-data="{
            slide: 0,
            total: {{ $slides->count() }},
            timer: null,
            next() {
                this.slide = (this.slide + 1) % this.total
            },
            prev() {
                this.slide = (this.slide - 1 + this.total) % this.total
            },
            go(index) {
                this.slide = index
                this.start()
            },
            start() {
                this.stop()
                this.timer = setInterval(() => this.next(), 10000)
            },
            stop() {
                if (this.timer) {
                    clearInterval(this.timer)
                    this.timer = null
                }
            }
        }"
        x-init="start()"
        @mouseenter="stop()"
        @mouseleave="start()"
        @keydown.right.prevent="next()"
        @keydown.left.prevent="prev()"
        tabindex="0"
        aria-label="PSA featured content"
        aria-roledescription="carousel"
        class="relative min-h-screen overflow-hidden bg-white pt-16 outline-none"
    >
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-white via-blue-50/40 to-cyan-50/50"></div>
            <div class="absolute -right-40 -top-40 h-[500px] w-[500px] rounded-full bg-blue-100/50"></div>
            <div class="absolute -bottom-48 -left-48 h-[550px] w-[550px] rounded-full bg-cyan-100/40"></div>
            <div class="absolute right-[12%] top-[18%] h-4 w-4 rounded-full bg-blue-400/30"></div>
            <div class="absolute left-[8%] top-[35%] h-3 w-3 rounded-full bg-cyan-400/30"></div>
            <div class="absolute bottom-[25%] right-[7%] h-5 w-5 rounded-full bg-blue-300/30"></div>

            <svg class="absolute left-0 top-0 h-full w-full opacity-[0.07]" viewBox="0 0 1440 900" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g stroke="#2563EB" stroke-width="3">
                    <rect x="105" y="130" width="80" height="30" rx="8" />
                    <rect x="130" y="105" width="30" height="80" rx="8" />
                    <path d="M120 290 C120 350 150 390 205 390 C260 390 290 350 290 290" />
                    <path d="M120 290V250" />
                    <path d="M290 290V250" />
                    <circle cx="120" cy="240" r="14" />
                    <circle cx="290" cy="240" r="14" />
                    <path d="M205 390V425" />
                    <circle cx="205" cy="450" r="25" />
                    <path d="M205 425V450" />
                    <circle cx="1280" cy="350" r="60" />
                    <circle cx="1280" cy="350" r="42" />
                    <circle cx="1280" cy="350" r="18" />
                </g>

                <path d="M0 690 H160 L190 690 L215 620 L240 760 L270 690 H430 L460 690 L485 640 L510 730 L535 690 H700 L730 690 L755 610 L780 770 L810 690 H970 L1000 690 L1025 645 L1050 735 L1075 690 H1250 L1280 690 L1305 625 L1330 755 L1360 690 H1440" stroke="#2563EB" stroke-width="3" />

                <g stroke="#06B6D4" stroke-width="2">
                    <path d="M1120 100 C1180 140 1180 210 1120 250" />
                    <path d="M1200 100 C1140 140 1140 210 1200 250" />
                    <line x1="1140" y1="125" x2="1180" y2="125" />
                    <line x1="1135" y1="155" x2="1185" y2="155" />
                    <line x1="1135" y1="190" x2="1185" y2="190" />
                    <line x1="1140" y1="225" x2="1180" y2="225" />
                    <path d="M430 170h35M447.5 152.5v35" />
                    <path d="M960 280h35M977.5 262.5v35" />
                    <path d="M1330 540h35M1347.5 522.5v35" />
                </g>
            </svg>

            <svg class="absolute inset-0 h-full w-full opacity-[0.035]" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="medical-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1.5" fill="#2563EB" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#medical-grid)" />
            </svg>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            <div class="relative grid">
                @foreach ($slides as $index => $slide)
                    <div
                        x-show="slide === {{ $index }}"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0 translate-x-6"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-500"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0 -translate-x-6"
                        class="col-start-1 row-start-1"
                        style="{{ $index === 0 ? '' : 'display: none;' }}"
                        aria-label="{{ trim($slide->title . ' ' . $slide->highlight) }}"
                    >
                        @php
                            $imageUrls = $slide->image_urls;
                        @endphp

                        <div class="relative grid items-center gap-12 overflow-hidden {{ $imageUrls !== [] ? 'lg:grid-cols-[0.85fr_1.15fr] lg:gap-12' : 'lg:grid-cols-1' }}">
                            <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-blue-100/60 blur-3xl"></div>
                            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-72 w-72 rounded-full bg-cyan-100/50 blur-3xl"></div>

                            <div class="relative z-10 max-w-2xl {{ $imageUrls === [] ? 'lg:max-w-4xl' : '' }}">
                                @if (filled($slide->eyebrow))
                                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700">
                                        <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                                        {{ $slide->eyebrow }}
                                    </div>
                                @endif

                                <h1 class="font-display text-4xl font-extrabold leading-[1.05] tracking-tight text-slate-950 sm:text-5xl lg:text-6xl xl:text-7xl">
                                    {{ $slide->title }}
                                    @if (filled($slide->highlight))
                                        <span class="block text-blue-600">{{ $slide->highlight }}</span>
                                    @endif
                                </h1>

                                @if (filled($slide->description))
                                    <p class="mt-6 max-w-xl text-base leading-8 text-slate-500 sm:text-lg">
                                        {{ $slide->description }}
                                    </p>
                                @endif

                                @php
                                    $details = collect(is_array($slide->details) ? $slide->details : [])
                                        ->filter(fn ($detail) => filled($detail['label'] ?? null) && filled($detail['value'] ?? null))
                                        ->take(4)
                                        ->values();
                                @endphp

                                @if ($details->isNotEmpty())
                                    <div class="mt-7 grid gap-3 sm:grid-cols-2">
                                        @foreach ($details as $detail)
                                            @php
                                                $detailLabel = strtolower((string) $detail['label']);
                                            @endphp
                                            <div class="{{ $details->count() === 1 ? 'sm:col-span-2' : '' }} rounded-xl border border-slate-200 bg-white p-4">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                                        @if (str_contains($detailLabel, 'date') || str_contains($detailLabel, 'deadline'))
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                                                            </svg>
                                                        @elseif (str_contains($detailLabel, 'venue') || str_contains($detailLabel, 'location'))
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l2-8 4 16 2-8h6" />
                                                            </svg>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                                            {{ $detail['label'] }}
                                                        </p>
                                                        <p class="mt-1 text-sm font-bold leading-snug text-slate-800 sm:text-base">
                                                            {{ $detail['value'] }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @php
                                    $primaryUrl = $slide->primary_href;
                                    $secondaryUrl = $slide->secondary_href;
                                    $primaryIsPdf = filled($primaryUrl) && str_ends_with(strtolower((string) parse_url($primaryUrl, PHP_URL_PATH)), '.pdf');
                                    $secondaryIsPdf = filled($secondaryUrl) && str_ends_with(strtolower((string) parse_url($secondaryUrl, PHP_URL_PATH)), '.pdf');
                                @endphp

                                @if ((filled($slide->primary_label) && filled($primaryUrl)) || (filled($slide->secondary_label) && filled($secondaryUrl)))
                                    <div class="mt-8 flex flex-wrap gap-3">
                                        @if (filled($slide->primary_label) && filled($primaryUrl))
                                            <a
                                                href="{{ $primaryUrl }}"
                                                class="{{ $primaryIsPdf ? 'inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-600 transition-all hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-100 hover:text-red-700' : 'inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition-all hover:-translate-y-0.5 hover:bg-blue-700' }}"
                                            >
                                                @if ($primaryIsPdf)
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                                                        <path d="M5.25 2.25A2.25 2.25 0 0 0 3 4.5v15A2.25 2.25 0 0 0 5.25 21.75h13.5A2.25 2.25 0 0 0 21 19.5v-15a2.25 2.25 0 0 0-2.25-2.25H5.25ZM7.5 8.25h2.25a2.25 2.25 0 0 1 0 4.5H9v2.25H7.5v-6.75Zm1.5 1.5v1.5h.75a.75.75 0 0 0 0-1.5H9Zm4.125-1.5h1.5a2.625 2.625 0 0 1 0 5.25h-.75V15h-1.5V8.25h.75Zm.75 1.5v2.25h.75a1.125 1.125 0 0 0 0-2.25h-.75Z" />
                                                    </svg>
                                                @endif
                                                {{ $slide->primary_label }}
                                                @if ($primaryIsPdf)
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                                    </svg>
                                                @endif
                                            </a>
                                        @endif

                                        @if (filled($slide->secondary_label) && filled($secondaryUrl))
                                            <a
                                                href="{{ $secondaryUrl }}"
                                                class="{{ $secondaryIsPdf ? 'inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-600 transition-all hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-100 hover:text-red-700' : 'inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition-all hover:border-blue-300 hover:text-blue-600' }}"
                                            >
                                                @if ($secondaryIsPdf)
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                                                        <path d="M5.25 2.25A2.25 2.25 0 0 0 3 4.5v15A2.25 2.25 0 0 0 5.25 21.75h13.5A2.25 2.25 0 0 0 21 19.5v-15a2.25 2.25 0 0 0-2.25-2.25H5.25ZM7.5 8.25h2.25a2.25 2.25 0 0 1 0 4.5H9v2.25H7.5v-6.75Zm1.5 1.5v1.5h.75a.75.75 0 0 0 0-1.5H9Zm4.125-1.5h1.5a2.625 2.625 0 0 1 0 5.25h-.75V15h-1.5V8.25h.75Zm.75 1.5v2.25h.75a1.125 1.125 0 0 0 0-2.25h-.75Z" />
                                                    </svg>
                                                @endif
                                                {{ $slide->secondary_label }}
                                                @if ($secondaryIsPdf)
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                                                    </svg>
                                                @endif
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>


                            @if ($imageUrls !== [])

                            <div class="relative z-10 flex justify-center lg:justify-end">
                                <div class="absolute inset-10 rounded-full bg-blue-100/70 blur-3xl"></div>

                                <div
                                    x-data="{
                                        image: 0,
                                        total: {{ count($imageUrls) }},
                                        timer: null,
                                        nextImage() {
                                            this.image = (this.image + 1) % this.total
                                        },
                                        previousImage() {
                                            this.image = (this.image - 1 + this.total) % this.total
                                        },
                                        goToImage(index) {
                                            this.image = index
                                            this.startImage()
                                        },
                                        startImage() {
                                            if (this.total < 2) {
                                                return
                                            }

                                            this.stopImage()
                                            this.timer = setInterval(() => this.nextImage(), 7000)
                                        },
                                        stopImage() {
                                            if (this.timer) {
                                                clearInterval(this.timer)
                                                this.timer = null
                                            }
                                        }
                                    }"
                                    x-init="startImage()"
                                    @mouseenter="stopImage()"
                                    @mouseleave="startImage()"
                                    class="group relative flex min-h-[320px] w-full items-center justify-center sm:min-h-[440px] lg:min-h-[540px]"
                                >
                                    @foreach ($imageUrls as $imageIndex => $imageUrl)
                                        <img
                                            x-show="image === {{ $imageIndex }}"
                                            x-transition:enter="transition ease-out duration-500"
                                            x-transition:enter-start="opacity-0 translate-x-4"
                                            x-transition:enter-end="opacity-100 translate-x-0"
                                            x-transition:leave="transition ease-in duration-300 absolute"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0 -translate-x-4"
                                            src="{{ $imageUrl }}"
                                            alt="{{ $slide->image_alt }}"
                                            loading="{{ $index === 0 && $imageIndex === 0 ? 'eager' : 'lazy' }}"
                                            style="{{ $imageIndex === 0 ? '' : 'display: none;' }}"
                                            :aria-hidden="image !== {{ $imageIndex }}"
                                            class="mx-auto max-h-[540px] w-auto max-w-full rounded-xl object-contain transition duration-700 ease-out group-hover:scale-[1.02]"
                                        />
                                    @endforeach

                                    @if (count($imageUrls) > 1)
                                        <button
                                            type="button"
                                            @click.stop="previousImage()"
                                            aria-label="Previous hero image"
                                            class="absolute left-0 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-slate-500 shadow-sm backdrop-blur transition hover:text-blue-600"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                                            </svg>
                                        </button>

                                        <div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-white/80 px-2.5 py-1.5 backdrop-blur">
                                            @foreach ($imageUrls as $imageIndex => $imageUrl)
                                                <button
                                                    type="button"
                                                    @click.stop="goToImage({{ $imageIndex }})"
                                                    :class="image === {{ $imageIndex }} ? 'w-5 bg-blue-600' : 'w-1.5 bg-slate-300'"
                                                    class="h-1.5 rounded-full transition-all duration-300"
                                                    aria-label="Show hero image {{ $imageIndex + 1 }}"
                                                ></button>
                                            @endforeach
                                        </div>

                                        <button
                                            type="button"
                                            @click.stop="nextImage()"
                                            aria-label="Next hero image"
                                            class="absolute right-0 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/85 text-slate-500 shadow-sm backdrop-blur transition hover:text-blue-600"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 flex items-center justify-center gap-5 sm:mt-14">
                <button
                    type="button"
                    @click="prev()"
                    aria-label="Previous slide"
                    class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition-all hover:border-blue-300 hover:text-blue-600 hover:shadow-md"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                    </svg>
                </button>

                <div class="flex items-center gap-2" role="tablist" aria-label="Hero slides">
                    @foreach ($slides as $index => $slide)
                        <button
                            type="button"
                            @click="go({{ $index }})"
                            :class="slide === {{ $index }} ? 'w-8 bg-blue-600' : 'w-2 bg-slate-300'"
                            class="h-2 rounded-full transition-all duration-300"
                            aria-label="Go to {{ trim($slide->title . ' ' . $slide->highlight) }} slide"
                            :aria-selected="slide === {{ $index }}"
                            role="tab"
                        ></button>
                    @endforeach
                </div>

                <button
                    type="button"
                    @click="next()"
                    aria-label="Next slide"
                    class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition-all hover:border-blue-300 hover:text-blue-600 hover:shadow-md"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            </div>
        </div>
    </section>
@else
    <section class="relative overflow-hidden bg-white px-4 pb-16 pt-28 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl rounded-2xl border border-blue-100 bg-white p-8 shadow-xl shadow-blue-100/50 sm:p-14">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Philippine Society of Anesthesiologists</p>
            <h1 class="mt-4 max-w-3xl font-display text-4xl font-extrabold leading-tight text-slate-950 sm:text-6xl">
                Advancing anesthesia care through excellence, education, and innovation.
            </h1>
            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                Discover the latest PSA programs, events, and announcements.
            </p>
        </div>
    </section>
@endif