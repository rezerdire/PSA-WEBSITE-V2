<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<section
    x-data="{
        slide: 0,
        total: 4,
        timer: null,

        next() {
            this.slide = (this.slide + 1) % this.total
        },

        prev() {
            this.slide = (this.slide - 1 + this.total) % this.total
        },

        go(i) {
            this.slide = i
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
    class="relative min-h-screen overflow-hidden bg-white pt-16"
>

    {{-- med bg --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">

        {{-- Soft medical blue gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-white via-blue-50/40 to-cyan-50/50"></div>

        {{-- Large decorative circles --}}
        <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-blue-100/50"></div>
        <div class="absolute -bottom-48 -left-48 h-[550px] w-[550px] rounded-full bg-cyan-100/40"></div>

        {{-- Small floating circles --}}
        <div class="absolute top-[18%] right-[12%] h-4 w-4 rounded-full bg-blue-400/30"></div>
        <div class="absolute top-[35%] left-[8%] h-3 w-3 rounded-full bg-cyan-400/30"></div>
        <div class="absolute bottom-[25%] right-[7%] h-5 w-5 rounded-full bg-blue-300/30"></div>

   {{-- line art from my BFF --}}
        <svg
            class="absolute left-0 top-0 h-full w-full opacity-[0.07]"
            viewBox="0 0 1440 900"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >

            {{-- Medical cross --}}
            <g stroke="#2563EB" stroke-width="3">
                <rect x="105" y="130" width="80" height="30" rx="8"/>
                <rect x="130" y="105" width="30" height="80" rx="8"/>
            </g>

            {{-- Stethoscope --}}
            <g stroke="#2563EB" stroke-width="3">
                <path
                    d="M120 290
                       C120 350 150 390 205 390
                       C260 390 290 350 290 290"
                />
                <path d="M120 290V250"/>
                <path d="M290 290V250"/>
                <circle cx="120" cy="240" r="14"/>
                <circle cx="290" cy="240" r="14"/>
                <path d="M205 390V425"/>
                <circle cx="205" cy="450" r="25"/>
                <path d="M205 425V450"/>
            </g>

            {{-- ECG --}}
            <path
                d="
                    M0 690
                    H160
                    L190 690
                    L215 620
                    L240 760
                    L270 690
                    H430
                    L460 690
                    L485 640
                    L510 730
                    L535 690
                    H700
                    L730 690
                    L755 610
                    L780 770
                    L810 690
                    H970
                    L1000 690
                    L1025 645
                    L1050 735
                    L1075 690
                    H1250
                    L1280 690
                    L1305 625
                    L1330 755
                    L1360 690
                    H1440
                "
                stroke="#2563EB"
                stroke-width="3"
            />

            {{-- DNA / molecule style medical decoration --}}
            <g stroke="#06B6D4" stroke-width="2">
                <path d="M1120 100 C1180 140 1180 210 1120 250"/>
                <path d="M1200 100 C1140 140 1140 210 1200 250"/>

                <line x1="1140" y1="125" x2="1180" y2="125"/>
                <line x1="1135" y1="155" x2="1185" y2="155"/>
                <line x1="1135" y1="190" x2="1185" y2="190"/>
                <line x1="1140" y1="225" x2="1180" y2="225"/>
            </g>

            {{-- Medical pulse circles --}}
            <g stroke="#2563EB" stroke-width="2">
                <circle cx="1280" cy="350" r="60"/>
                <circle cx="1280" cy="350" r="42"/>
                <circle cx="1280" cy="350" r="18"/>
            </g>

            {{-- Small medical plus signs --}}
            <g stroke="#06B6D4" stroke-width="3">
                <path d="M430 170h35M447.5 152.5v35"/>
                <path d="M960 280h35M977.5 262.5v35"/>
                <path d="M1330 540h35M1347.5 522.5v35"/>
            </g>

        </svg>

        {{-- Dot grid --}}
        <svg
            class="absolute inset-0 h-full w-full opacity-[0.035]"
            xmlns="http://www.w3.org/2000/svg"
        >
            <defs>
                <pattern
                    id="medical-grid"
                    width="32"
                    height="32"
                    patternUnits="userSpaceOnUse"
                >
                    <circle
                        cx="2"
                        cy="2"
                        r="1.5"
                        fill="#2563EB"
                    />
                </pattern>
            </defs>

            <rect
                width="100%"
                height="100%"
                fill="url(#medical-grid)"
            />
        </svg>

    </div>


{{-- hero section --}}
    <div class="relative z-10 mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">

 {{-- slide start --}}
        <div class="relative grid">

          {{-- slide 0 --}}
            <div
                x-show="slide === 0"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 -translate-x-4"
                class="col-start-1 row-start-1"
                style="display: none;"
            >

                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">

                    {{-- details/text --}}
                    <div class="max-w-2xl">

                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-red-100 bg-red-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-red-600">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-red-600"></span>
                            </span>

                            Live Competition
                        </div>

                        <p class="mb-3 text-sm font-bold uppercase tracking-[0.25em] text-blue-600">
                            Philippines Anesthesia Crisis Competition 2026
                        </p>

                        <h1 class="font-display text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                            Anesthesia
                            <span class="text-blue-600">Sim Wars</span>
                            Trilogy
                        </h1>

                        <div class="mt-4 flex items-center gap-3">
                            <span class="h-px w-10 bg-blue-500"></span>
                            <p class="text-lg font-semibold text-slate-500 sm:text-xl">
                                Competition, Episode 1
                            </p>
                        </div>

                        <p class="mt-6 max-w-xl text-base leading-8 text-slate-500 sm:text-lg">
                            27 teams. One mission. Join the PSA's simulation-based
                            competition for anesthesiologists as hospitals nationwide
                            compete to advance anesthesia care across the Philippines.
                        </p>

                        {{-- card feature --}}
                        <div class="mt-8 grid max-w-lg grid-cols-3 gap-3">

                            <div class="rounded-2xl border border-blue-100 bg-white/80 p-4 shadow-sm backdrop-blur">
                                <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 2v20"/>
                                        <path d="M2 12h20"/>
                                    </svg>
                                </div>

                                <p class="text-xs font-semibold text-slate-500">
                                    Simulation
                                </p>
                            </div>

                            <div class="rounded-2xl border border-cyan-100 bg-white/80 p-4 shadow-sm backdrop-blur">
                                <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 12h4l2-8 4 16 2-8h6"/>
                                    </svg>
                                </div>

                                <p class="text-xs font-semibold text-slate-500">
                                    Clinical Skills
                                </p>
                            </div>

                            <div class="rounded-2xl border border-blue-100 bg-white/80 p-4 shadow-sm backdrop-blur">
                                <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                </div>

                                <p class="text-xs font-semibold text-slate-500">
                                    Teamwork
                                </p>
                            </div>

                        </div>
                        {{-- end card feature --}}
                    </div>


                    {{-- Image right side --}}
                    <div class="relative flex justify-center lg:justify-end">

                        {{-- Glow design --}}
                        <div class="absolute inset-10 rounded-full bg-blue-200/40 blur-3xl"></div>

                        <div class="relative overflow-hidden rounded-[2rem] border border-white/70 bg-white/70 p-3 shadow-2xl shadow-blue-100 backdrop-blur">

                            <img
                                src="{{ asset('images/simwars-competition.png') }}"
                                alt="Anesthesia Sim Wars Trilogy Competition, Episode 1 - Philippines Anesthesia Crisis Competition 2026"
                                class="relative max-h-[540px] w-auto rounded-[1.5rem] object-contain"
                            />

                        </div>

                    </div>

                </div>

            </div>


            {{-- slide === 1 interesting case --}}
            <div
                x-show="slide === 1"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 -translate-x-4"
                class="col-start-1 row-start-1"
                style="display: none;"
            >

                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">

                    <div class="max-w-2xl">

                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700">
                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                            Call for Entries
                        </div>

                        <p class="mb-3 text-sm font-bold uppercase tracking-[0.25em] text-cyan-600">
                            Clinical Knowledge & Innovation
                        </p>

                        <h1 class="font-display text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                            PSA
                            <span class="text-blue-600">
                                Interesting Case
                            </span>
                            Competition
                            <span class="block text-slate-400">2026</span>
                        </h1>

                        <p class="mt-6 max-w-xl text-base leading-8 text-slate-500 sm:text-lg">
                            Share your unique and interesting clinical cases,
                            engage with colleagues, and inspire learning through
                            knowledge, experience, and innovation.
                        </p>

                        <div class="mt-5 flex items-start gap-3 rounded-2xl border border-blue-100 bg-blue-50/70 p-4">

                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="19"
                                    height="19"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/>
                                    <path d="M3 9h18"/>
                                    <path d="M9 21V9"/>
                                </svg>
                            </div>

                            <p class="text-sm leading-6 text-slate-600">
                                We are now accepting case reports for the PSA
                                Interesting Case Competition 2026.
                                <strong class="text-slate-800">
                                    Deadline: August 28, 2026.
                                </strong>
                            </p>

                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">

                            <a
                                href="https://compose.mail.yahoo.com/?to=psainc_sec@yahoo.com"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition-all hover:-translate-y-0.5 hover:bg-blue-700"
                            >
                                Submit Your Entry

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="17"
                                    height="17"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </a>

                            <a
                                href="{{ route('Interesting-Case') }}"
                                class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition-all hover:border-blue-300 hover:text-blue-600"
                            >
                                Learn More
                            </a>

                        </div>

                    </div>


                    <div class="relative flex justify-center lg:justify-end">

                        <div class="absolute inset-10 rounded-full bg-cyan-100/60 blur-3xl"></div>

                        <div class="relative rounded-[2rem] border border-white/70 bg-white/70 p-3 shadow-2xl shadow-blue-100 backdrop-blur">

                            <img
                                src="{{ asset('images/InterestingCase.png') }}"
                                alt="PSA Interesting Case Competition 2026"
                                class="max-h-[540px] w-auto rounded-[1.5rem] object-contain"
                            />

                        </div>

                    </div>

                </div>

            </div>


            {{-- slide === 2 annual convention --}}
            <div
                x-show="slide === 2"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 -translate-x-4"
                class="col-start-1 row-start-1"
                style="display: none;"
            >

                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">

                    <div class="max-w-2xl">

                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700">
                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                            Upcoming Event
                        </div>

                        <p class="mb-3 text-sm font-bold uppercase tracking-[0.25em] text-cyan-600">
                            Philippine Society of Anesthesiologists
                        </p>

                        <h1 class="font-display text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                            PSA
                            <span class="text-blue-600">
                                58<sup class="text-2xl sm:text-3xl">th</sup>
                            </span>
                            Annual Convention
                        </h1>

                        <div class="mt-5 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M3 12h4l2-8 4 16 2-8h6"/>
                                </svg>
                            </div>

                            <p class="text-lg font-semibold text-slate-600">
                                Beyond the Operating Room:
                                <span class="text-blue-600">
                                    Innovation, Safety, Excellence
                                </span>
                            </p>
                        </div>

                        <p class="mt-5 text-sm leading-7 text-slate-500 sm:text-base">
                            Marriott Grand Ballroom, Pasay City
                            <span class="mx-1 text-slate-300">•</span>
                            November 25–27, 2026
                        </p>

                        <div class="mt-8">

                            <a
                                href="{{ route('annual-convention-poster') }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition-all hover:-translate-y-0.5 hover:bg-blue-700"
                            >
                                Check the Registration Rates

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="17"
                                    height="17"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </a>

                        </div>

                    </div>


                    <div class="relative flex justify-center lg:justify-end">

                        <div class="absolute inset-10 rounded-full bg-blue-100/70 blur-3xl"></div>

                        <div class="relative rounded-[2rem] border border-white/70 bg-white/70 p-3 shadow-2xl shadow-blue-100 backdrop-blur">

                            <img
                                src="{{ asset('annual-convention/annualposter.png') }}"
                                alt="PSA 58th Annual Convention Poster"
                                class="max-h-[540px] w-auto rounded-[1.5rem] object-contain"
                            />

                        </div>

                    </div>

                </div>

            </div>


            {{-- slide === 3 Default hero section page --}}
            <div
                x-show="slide === 3"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 -translate-x-4"
                class="col-start-1 row-start-1"
                style="display: none;"
            >

                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">

                    {{-- Main copy --}}
                    <div class="max-w-2xl">

                        {{-- Medical badge --}}
                        <div class="mb-7 inline-flex items-center gap-3 rounded-full border border-blue-100 bg-white/80 px-4 py-2 shadow-sm backdrop-blur">

                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 2v20"/>
                                    <path d="M2 12h20"/>
                                </svg>

                            </span>

                            <span class="text-xs font-bold uppercase tracking-[0.18em] text-slate-600">
                                Advancing Anesthesia Care
                            </span>

                        </div>


                        <h1 class="font-display text-4xl font-semibold leading-[1.08] text-slate-900 sm:text-5xl lg:text-7xl">

                            Philippine Society
                            <span class="block text-slate-400">
                                of
                            </span>

                            <span class="text-blue-600">
                                Anesthesiologists
                            </span>

                        </h1>


                        <p class="mt-7 max-w-xl text-base leading-8 text-slate-500 sm:text-lg">
                            Promoting safe and quality anesthesia care across the nation.
                            A community of world-class Filipino anesthesiologists driven
                            by excellence, education, innovation, and patient safety.
                        </p>


                        {{-- Buttons --}}
                        <div class="mt-8 flex flex-wrap gap-3">

                            <a
                                href="{{ asset('Membership Form.pdf') }}"
                                download
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition-all hover:-translate-y-0.5 hover:bg-blue-700"
                            >
                                Download Membership Form

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    width="19"
                                    height="19"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/>
                                    <path d="M14 2v5a1 1 0 0 0 1 1h5"/>
                                    <path d="M12 18v-6"/>
                                    <path d="m9 15 3 3 3-3"/>
                                </svg>

                            </a>


                            <a
                                href="#recent-events"
                                class="inline-flex items-center rounded-xl border border-slate-200 bg-white/80 px-5 py-3 text-sm font-semibold text-slate-700 backdrop-blur transition-all hover:border-blue-300 hover:text-blue-600"
                            >
                                Explore Our Events
                            </a>

                        </div>


                        {{-- stats --}}
                        <div class="mt-10 grid grid-cols-3 gap-3 border-t border-slate-200/70 pt-7 sm:mt-14 sm:gap-5 sm:pt-8">

                            <div class="rounded-2xl border border-blue-100 bg-white/70 p-3 shadow-sm backdrop-blur sm:p-5">

                                <p class="font-display text-2xl font-semibold text-slate-900 sm:text-3xl">
                                    70+
                                </p>

                                <p class="mt-1 text-[9px] font-semibold uppercase leading-tight tracking-wider text-slate-400 sm:text-xs">
                                    Years of Service
                                </p>

                            </div>


                            <div class="rounded-2xl border border-blue-100 bg-white/70 p-3 shadow-sm backdrop-blur sm:p-5">

                                <p class="font-display text-2xl font-semibold text-slate-900 sm:text-3xl">
                                    6,000+
                                </p>

                                <p class="mt-1 text-[9px] font-semibold uppercase leading-tight tracking-wider text-slate-400 sm:text-xs">
                                    Members Nationwide
                                </p>

                            </div>


                            <div class="rounded-2xl border border-blue-100 bg-white/70 p-3 shadow-sm backdrop-blur sm:p-5">

                                <p class="font-display text-2xl font-semibold text-slate-900 sm:text-3xl">
                                    32
                                </p>

                                <p class="mt-1 text-[9px] font-semibold uppercase leading-tight tracking-wider text-slate-400 sm:text-xs">
                                    Regional Chapters
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- med panel from my bff --}}
                    <div class="relative hidden min-h-[500px] items-center justify-center lg:flex">

                        {{-- circle design --}}
                        <div class="absolute h-[430px] w-[430px] rounded-full bg-gradient-to-br from-blue-100 to-cyan-50"></div>

                        {{-- ring in the middle --}}
                        <div class="absolute h-[500px] w-[500px] rounded-full border border-blue-100"></div>

                        {{-- Inner ring --}}
                        <div class="absolute h-[360px] w-[360px] rounded-full border border-cyan-100"></div>


                        {{-- card (only for default hero section) --}}
                        <div class="relative z-10 w-[360px] rounded-[2rem] border border-white bg-white/90 p-7 shadow-2xl shadow-blue-200/60 backdrop-blur">

                            {{-- Header --}}
                            <div class="flex items-center justify-between">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                                      {{-- icon guard --}}
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            width="25"
                                            height="25"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                            <path d="M12 8v6"/>
                                            <path d="M9 11h6"/>
                                        </svg>

                                    </div>

                                    <div>
                                        <p class="text-sm font-bold text-slate-900">
                                            Patient Safety
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            Our commitment
                                        </p>
                                    </div>

                                </div>

                                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>

                            </div>


                            {{-- line graph --}}
                            <div class="mt-8 overflow-hidden rounded-2xl bg-slate-50 p-5">

                                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Clinical Excellence
                                </p>

                                <svg viewBox="0 0 500 120" class="h-24 w-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d=" M0 60
                                            H90
                                            L110 60
                                            L130 25
                                            L150 95
                                            L175 60
                                            H250
                                            L270 60
                                            L290 35
                                            L310 85
                                            L330 60
                                            H420
                                            L440 60
                                            L460 20
                                            L480 100
                                            L500 60
                                        "
                                        stroke="#2563EB"
                                        stroke-width="4"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                </svg>

                            </div>


                            {{-- Medical pillars --}}
                            <div class="mt-5 grid grid-cols-3 gap-3">

                                <div class="rounded-xl bg-blue-50 p-3 text-center">
                                    <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-lg bg-white text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 2v20"/>
                                            <path d="M2 12h20"/>
                                        </svg>
                                    </div>

                                    <p class="mt-2 text-[10px] font-semibold text-slate-500">
                                        Safety
                                    </p>
                                </div>


                                <div class="rounded-xl bg-cyan-50 p-3 text-center">
                                    <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-lg bg-white text-cyan-600">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M3 12h4l2-8 4 16 2-8h6"/>
                                        </svg>
                                    </div>

                                    <p class="mt-2 text-[10px] font-semibold text-slate-500">
                                        Excellence
                                    </p>
                                </div>


                                <div class="rounded-xl bg-blue-50 p-3 text-center">
                                    <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-lg bg-white text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>
                                    </div>

                                    <p class="mt-2 text-[10px] font-semibold text-slate-500">
                                        Community
                                    </p>
                                </div>

                            </div>

                        </div>


                        {{-- Floating medical badge --}}
                        <div class="absolute right-0 top-10 z-20 rounded-2xl border border-white bg-white/90 px-4 py-3 shadow-xl backdrop-blur">

                            <div class="flex items-center gap-3">

                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5"/>
                                    </svg>

                                </div>

                                <div>
                                    <p class="text-xs font-bold text-slate-800">
                                        Quality Care
                                    </p>

                                    <p class="text-[10px] text-slate-400">
                                        Patient-centered
                                    </p>
                                </div>

                            </div>

                        </div>


                        {{-- Floating stethoscope badge --}}
                        <div class="absolute bottom-12 left-0 z-20 flex h-16 w-16 items-center justify-center rounded-2xl border border-white bg-white/90 text-blue-600 shadow-xl backdrop-blur">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                width="28"
                                height="28"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M6 3v5a6 6 0 0 0 12 0V3"/>
                                <path d="M6 8H3"/>
                                <path d="M18 8h3"/>
                                <path d="M12 14v3"/>
                                <circle cx="12" cy="20" r="3"/>
                            </svg>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            SLIDER CONTROLS
        ====================================================== --}}
        <div class="mt-10 flex items-center justify-center gap-5 sm:mt-14">

            {{-- Previous --}}
            <button
                @click="prev()"
                aria-label="Previous slide"
                class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition-all hover:border-blue-300 hover:text-blue-600 hover:shadow-md"
            >

                <svg xmlns="http://www.w3.org/2000/svg"
                    width="19"
                    height="19"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>

            </button>


            {{-- Indicators --}}
            <div class="flex items-center gap-2">

                <button
                    @click="go(0)"
                    :class="slide === 0 ? 'w-8 bg-blue-600' : 'w-2 bg-slate-300'"
                    class="h-2 rounded-full transition-all duration-300"
                    aria-label="Go to Sim Wars slide"
                ></button>

                <button
                    @click="go(1)"
                    :class="slide === 1 ? 'w-8 bg-blue-600' : 'w-2 bg-slate-300'"
                    class="h-2 rounded-full transition-all duration-300"
                    aria-label="Go to Interesting Case slide"
                ></button>

                <button
                    @click="go(2)"
                    :class="slide === 2 ? 'w-8 bg-blue-600' : 'w-2 bg-slate-300'"
                    class="h-2 rounded-full transition-all duration-300"
                    aria-label="Go to Annual Convention slide"
                ></button>

                <button
                    @click="go(3)"
                    :class="slide === 3 ? 'w-8 bg-blue-600' : 'w-2 bg-slate-300'"
                    class="h-2 rounded-full transition-all duration-300"
                    aria-label="Go to PSA main slide"
                ></button>

            </div>


            {{-- Next --}}
            <button
                @click="next()"
                aria-label="Next slide"
                class="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition-all hover:border-blue-300 hover:text-blue-600 hover:shadow-md"
            >

                <svg xmlns="http://www.w3.org/2000/svg"
                    width="19"
                    height="19"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>

            </button>

        </div>

    </div>

</section>