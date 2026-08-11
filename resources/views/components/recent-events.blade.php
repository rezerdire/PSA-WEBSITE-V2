@php
    $events = [
        [
            'title' => 'PSA Research Forum 2026',
            'image' => 'images/researchforum2026.jpg',
            'badge' => 'Call for Abstracts',
            'date' => 'Deadline: August 20, 2026',
            'location' => 'Philippines',
            'route' => null,
        ],

        [
            'title' => 'SIM Wars Trilogy',
            'image' => 'images/event-cover-photo/SIMWARS-CP.jpg',
            'badge' => 'Upcoming',
            'date' => 'Novv 27, 2026 - Part 2 (Finals)',
            'location' => 'Marriott Grand Ballroom, Pasay City',
            'route' => 'sim-wars',
        ],

        [
            'title' => 'PSA Interesting Case Competition 2026',
            'image' => 'images/InterestingCase.png',
            'badge' => 'Upcoming',
            'date' => 'Deadline: August 28, 2026',
            'location' => 'Philippines',
            'route' => 'Interesting-Case',
        ],

        [
            'title' => 'PSA Review Program (PSARP)',
            'image' => 'images/event-cover-photo/PSARP-CP.png',
            'badge' => 'ON GOING',
            'date' => '2026',
            'location' => 'Philippines',
            'route' => null,
        ],
    ];

    $eventCount = count($events);
@endphp


<section id="recent-events" class="bg-white py-12 sm:py-16 lg:py-24" x-data="{
    index: 0,
    visible: 1,
    total: {{ $eventCount }},
    get maxIndex() {
        return Math.max(0, this.total - this.visible);
    },

    updateVisible() {
        if (window.innerWidth < 640) {
            this.visible = 1;
        } else if (window.innerWidth < 1024) {
            this.visible = 2;
        } else {
            this.visible = 3;
        }

        this.index = Math.min(this.index, this.maxIndex);
    },

    next() {
        if (this.index < this.maxIndex) {
            this.index++;
        }
    },

    previous() {
        if (this.index > 0) {
            this.index--;
        }
    },

    goTo(slide) {
        this.index = Math.max(
            0,
            Math.min(slide, this.maxIndex)
        );
    }
}" x-init=" updateVisible();
 window.addEventListener('resize', () => { updateVisible(); });">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- headersect --}}
        <div class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between lg:mb-12">
            <div>
                <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-blue-600 sm:mb-3 sm:text-xs">
                    Events
                </p>
                <h2 class="font-display text-2xl leading-tight text-slate-900 sm:text-4xl lg:text-5xl">
                    Recent Events
                </h2>
            </div>

            {{-- View All --}}
            <a href="{{ route('recent-event-list') }}"
                class="flex items-center gap-1 self-start text-xs font-semibold text-blue-600 transition-colors hover:text-blue-700 sm:self-auto sm:text-sm">
                View all events
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="m12 5 7 7-7 7" />
                </svg>
            </a>

        </div>

        {{-- event carousel --}}
        <div class="relative mb-8 overflow-hidden sm:mb-10">

            <div class="flex gap-4 transition-transform duration-500 ease-in-out sm:gap-5"
                :style="`
                            transform: translateX( calc(-${index} *((100% + ${visible === 1 ? 16 : 20}px) / ${visible})))`">

                {{-- dynamic events --}}
                @foreach ($events as $event)
                    <a href="{{ $event['route'] ? route($event['route']) : '#' }}"
                        class="group block flex-shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all duration-300
                               hover:-translate-y-1
                               hover:border-blue-200
                               hover:shadow-lg
                               hover:ring-1
                               hover:ring-blue-200
                               w-full
                               sm:w-[calc((100%-1rem)/2)]
                               lg:w-[calc((100%-2.5rem)/3)]">

                        {{-- image --}}
                        <div class="relative aspect-[16/10] w-full overflow-hiddenbg-slate-100">
                            <img src="{{ asset($event['image']) }}" alt="{{ $event['title'] }}"
                                class="h-full w-full object-cover
                                transition-transform
                                duration-500
                                group-hover:scale-105"
                                loading="lazy">


                            {{-- Image Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparentto-transparent">
                            </div>


                            {{-- Badge --}}
                            <span
                                class="absolute left-3 top-3 rounded-full bg-green-100 px-2.5 py-1 text-[9px] font-bold  uppercase tracking-wider text-green-700sm:px-3 sm:text-[10px]">
                                {{ $event['badge'] }}
                            </span>

                        </div>


                        {{-- card content --}}
                        <div class="p-4 sm:p-6">

                            {{-- Title --}}
                            <h3 class="mb-3 font-display text-base leading-tight text-slate-900 sm:text-xl">
                                {{ $event['title'] }}
                            </h3>
                            {{-- event details --}}
                            <div class="mb-4 space-y-2 text-xs text-slate-500 sm:text-sm">
                                {{-- DATE --}}
                                <div class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-blue-400" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />

                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>

                                    <span class="break-words">
                                        {{ $event['date'] }}
                                    </span>

                                </div>


                                {{-- LOCATION --}}
                                <div class="flex items-start gap-2">

                                    <svg class="mt-0.5 h-3.5 w-3.5
                                               flex-shrink-0
                                               text-blue-400"
                                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9
                                               a1.998 1.998 0 01-2.827 0
                                               l-4.244-4.243
                                               a8 8 0 1111.314 0z" />

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0
                                               3 3 0 016 0z" />
                                    </svg>

                                    <span class="break-words">
                                        {{ $event['location'] }}
                                    </span>

                                </div>

                            </div>

                            {{-- Learn More --}}
                            <span
                                class="flex items-center gap-1
                                       text-xs font-semibold
                                       text-blue-600
                                       sm:text-sm">
                                Learn More

                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="m12 5 7 7-7 7" />
                                </svg>
                            </span>

                        </div>

                    </a>
                @endforeach

            </div>

        </div>

        {{-- carousel next and prev button --}}
        <div class="flex items-center justify-center gap-4 sm:gap-5">
            {{-- PREVIOUS --}}
            <button type="button" @click="previous()" :disabled="index === 0"
                :class="index === 0 ?
                    'cursor-not-allowed opacity-40' :
                    'hover:border-blue-300
                hover: text - blue - 600
                hover: shadow - md '"
                class="flex h-10 w-10
                       items-center justify-center
                       rounded-full
                       border border-slate-200
                       bg-white
                       text-slate-500
                       shadow-sm
                       transition-all
                       sm:h-11 sm:w-11"
                aria-label="Previous event">

                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>

            </button>


            {{-- =====================================================
                 PAGINATION DOTS
            ====================================================== --}}
            <div class="flex items-center gap-1.5 sm:gap-2">

                <template x-for="i in (maxIndex + 1)" :key="i">

                    <button type="button" @click="goTo(i - 1)"
                        :class="index === i - 1 ?
                            'w-7 bg-blue-600 sm:w-8' :
                            'w-2 bg-slate-300'"
                        class="h-2 rounded-full
                               transition-all duration-300"
                        :aria-label="`Go to slide ${i}`"></button>

                </template>

            </div>


            {{-- NEXT --}}
            <button type="button" @click="next()" :disabled="index === maxIndex"
                :class="index === maxIndex ?
                    'cursor-not-allowed opacity-40' :
                    'hover:border-blue-300
                hover: text - blue - 600
                hover: shadow - md '"
                class="flex h-10 w-10
                       items-center justify-center
                       rounded-full
                       border border-slate-200
                       bg-white
                       text-slate-500
                       shadow-sm
                       transition-all
                       sm:h-11 sm:w-11"
                aria-label="Next event">

                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </button>
        </div>
    </div>
</section>
