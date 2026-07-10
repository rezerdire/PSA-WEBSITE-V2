<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<section id="mission-vision" class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="flex items-center justify-center gap-3 mb-3">
                <span class="w-10 h-1 bg-blue-600 rounded-full"></span>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                    Who We Are
                </p>
            </div>

            <h2 class="font-serif text-4xl lg:text-5xl text-slate-900">
                Our Mission & Vision
            </h2>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Mission -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-8 lg:p-10 border border-slate-100 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="flex items-start gap-5">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-100">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"stroke-width="2"viewBox="0 0 24 24"><path stroke-linecap="round"stroke-linejoin="round"d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-2">
                            Mission
                        </p>

                        <h3 class="text-2xl font-semibold text-slate-900 mb-4">
                            Our Purpose
                        </h3>
                        <p class="text-slate-600 leading-relaxed">
                            To promote and maintain a community of responsible
                            anesthesiologists who can practice safe and quality
                            anesthesia care in the pursuit of serving the interests
                            of its members, their patients, and the nation.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Vision -->
            <div class="bg-blue-600 rounded-3xl p-8 lg:p-10 text-white shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round"d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-200 mb-2">
                    Vision
                </p>
                <h3 class="text-2xl font-semibold mb-4">
                    Our Future
                </h3>
                <p class="text-blue-100 leading-relaxed">
                    A Society that envisions Filipino anesthesiologists as
                    world-class professionals pursuing the PSA Mission with a
                    deep sense of fulfillment and pride.
                </p>
            </div>

            <!-- Shared Values -->
            <div class="lg:col-span-3 bg-white rounded-3xl p-8 border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                    <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-1">Shared Values</p>
                        <h3 class="text-2xl font-semibold text-slate-900">
                            What Guides Us
                        </h3>
                    </div>
                    <div class="hidden lg:block w-px h-16 bg-slate-200"></div> {{-- Divider --}
                    {{-- Shared Values --}}
                    <div class="flex flex-wrap gap-3">
                        <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-50 border border-blue-100 text-blue-700 font-medium text-sm rounded-full">
                        Commitment to Quality Care
                        </span>
                        <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-50 border border-blue-100 text-blue-700 font-medium text-sm rounded-full">
                        Concern for Members
                        </span>
                        <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-50 border border-blue-100 text-blue-700 font-medium text-sm rounded-full">
                        Professional Growth
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PSA Hymn -->
        <div class="mt-10 flex justify-center">
            <a href="https://www.youtube.com/watch?v=hkIcSJ5enp8" class="inline-flex items-center gap-3 px-6 py-3 bg-white border border-slate-200 rounded-full text-slate-700 font-semibold text-sm hover:border-blue-300 hover:text-blue-600 transition-all shadow-sm">
                <span class="w-8 h-8 bg-red-50 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor"viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </span>
                Watch the PSA Hymn
            </a>
        </div>
        {{-- End of PSA Hymn --}}

    </div>
</section>