<?php
use Livewire\Component;
new class extends Component {
    //
};
?>

@props([
    'panels' => [],
])

<div class="max-w-6xl mx-auto px-6 py-14 sm:py-4  ">
    @foreach ($panels as $panel)
        <div x-show="activeTab === '{{ $panel['key'] }}'"
            @if (!$loop->first) x-cloak {{-- hide the raw structure if it loads --}} @endif
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0">

                @if (!empty($panel['title']) && empty($panel['list']))
            <div class="flex justify-center">
                <div class="py-2 px-6 my-2 bg-blue-500 rounded-xl text-sm">
                    <h2 class="text-center text-2xl font-bold text-white">
                        {{ $panel['title'] }}
                    </h2>
                </div>
            </div>
        @endif
            @if (!empty($panel['subtitle']))
                <p class="text-center text-sm text-gray-500 mb-10">
                    {{ $panel['subtitle'] }}
                </p>
            @endif

            {{-- YT --}}
            @if (!empty($panel['youtube']))
                <div class="w-full max-w-4xl mx-auto mt-2 px-6">
                    <div x-data="{ playing: false }" class="relative w-full aspect-video">
                        <div x-show="!playing" @click="playing = true"
                            class="absolute inset-0 cursor-pointer rounded-xl overflow-hidden">
                            <img src="https://img.youtube.com/vi/{{ $panel['youtube'] }}/maxresdefault.jpg"
                                alt="{{ $panel['title'] ?? 'Video' }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div
                                    class="w-16 h-12 bg-red-600 rounded-xl flex items-center justify-center shadow-lg hover:bg-red-500 transition-colors duration-150">
                                    <svg class="w-6 h-6 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <iframe x-show="playing" x-cloak src="https://www.youtube.com/embed/{{ $panel['youtube'] }}"
                            title="{{ $panel['title'] ?? 'Video' }}"
                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen referrerpolicy="strict-origin-when-cross-origin"
                            class="absolute inset-0 w-full h-full border-0 rounded-xl">
                        </iframe>
                    </div>
                </div>

         @elseif(!empty($panel['list']))
    {{-- MOST OUTSTANDING CHAPTERS --}}
    <div class="w-full max-w-5xl mx-auto">

        {{-- Section Intro --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 px-6 py-8 sm:px-10 sm:py-10 mb-8 shadow-xl">

            {{-- Decorative circles --}}
            <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-20 -left-10 w-48 h-48 rounded-full bg-white/5"></div>

            <div class="relative flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">

                {{-- Trophy Icon --}}
                <div class="flex-shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl bg-white/15 border border-white/20 backdrop-blur-sm shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor"
                        stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 21h8M12 17v4M7 4h10M5 4h14M5 4v2a5 5 0 005 5h4a5 5 0 005-5V4M5 6H3a2 2 0 002 2h1M19 6h2a2 2 0 01-2 2h-1M8 11a5 5 0 008 0" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                        {{ $panel['title'] }}
                    </h2>

                    <p class="mt-2 max-w-2xl text-sm sm:text-base leading-relaxed text-blue-100">
                        Celebrating the chapters that have demonstrated outstanding
                        leadership, service, and excellence throughout the years.
                    </p>
                </div>
            </div>
        </div>

     
        {{-- THHHHHHEEE LIST --}}
        <div class="hidden sm:block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Header --}}
            <div class="grid grid-cols-[140px_1fr] bg-slate-50 border-b border-slate-200">

                <div class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                    Year
                </div>

                <div class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                    Outstanding Chapter
                </div>

            </div>


            {{-- Rows --}}
            <div class="divide-y divide-slate-100">

                @foreach ($panel['list'] as $row)

                    @php
                        $isNoNominee = strtoupper(trim($row['chapter'])) === 'NO NOMINEES';
                    @endphp

                    <div class="group grid grid-cols-[140px_1fr] items-center transition-all duration-200
                        {{ $isNoNominee
                            ? 'bg-slate-50/70'
                            : 'bg-white hover:bg-blue-50/50' }}">

                        {{-- Year --}}
                        <div class="px-6 py-4">

                            <span class="inline-flex min-w-[74px] justify-center items-center rounded-xl
                                {{ $isNoNominee
                                    ? 'bg-slate-200 text-slate-500'
                                    : 'bg-blue-50 text-blue-700 group-hover:bg-blue-100' }}
                                px-3 py-2 text-sm font-bold transition-colors">

                                {{ $row['year'] }}

                            </span>

                        </div>


                        {{-- Chapter --}}
                        <div class="px-6 py-4">

                            @if ($isNoNominee)

                                <div class="flex items-center gap-3">

                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-200">
                                        <svg class="w-4 h-4 text-slate-500" fill="none"
                                            stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="font-medium text-slate-500">
                                            No nominees
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            No chapter was selected for recognition
                                        </p>
                                    </div>

                                </div>

                            @else

                                <div class="flex items-center gap-4">

                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">

                                        <svg class="w-5 h-5" fill="none"
                                            stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 15l2 2 4-4M12 3l2.5 5.1L20 9l-4 3.9.9 5.5L12 15.8 7.1 18.4 8 12.9 4 9l5.5-.9L12 3z" />
                                        </svg>

                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-800 group-hover:text-blue-700 transition-colors">
                                            {{ $row['chapter'] }}
                                        </p>

                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Most Outstanding Chapter
                                        </p>
                                    </div>

                                </div>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>
        </div>


        {{-- Mobile Cards --}}
        <div class="sm:hidden space-y-3">

            @foreach ($panel['list'] as $row)

                @php
                    $isNoNominee = strtoupper(trim($row['chapter'])) === 'NO NOMINEES';
                @endphp

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm
                    {{ $isNoNominee ? 'bg-slate-50' : '' }}">

                    <div class="flex items-center gap-4">

                        {{-- Year --}}
                        <div class="flex-shrink-0 text-center">

                            <div class="flex h-14 w-14 items-center justify-center rounded-xl
                                {{ $isNoNominee
                                    ? 'bg-slate-200 text-slate-500'
                                    : 'bg-blue-50 text-blue-700' }}">

                                <span class="text-sm font-bold">
                                    {{ $row['year'] }}
                                </span>

                            </div>

                        </div>


                        {{-- Chapter --}}
                        <div class="min-w-0 flex-1">

                            @if ($isNoNominee)

                                <p class="font-medium text-slate-500">
                                    No nominees
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    No chapter was selected
                                </p>

                            @else

                                <div class="flex items-center gap-2">

                                    <svg class="w-4 h-4 flex-shrink-0 text-blue-600"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.539 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 00.95-.69l1.07-3.292z" />
                                    </svg>

                                    <p class="font-semibold text-slate-800 leading-snug">
                                        {{ $row['chapter'] }}
                                    </p>

                                </div>

                                <p class="mt-1 text-xs text-slate-400">
                                    Most Outstanding Chapter
                                </p>

                            @endif

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

            @elseif(!empty($panel['image']))
                @if (!empty($panel['link']))
                    <a href="{{ $panel['link'] }}" class="block">
                        <img src="{{ $panel['image'] }}" alt="{{ $panel['alt'] ?? '' }}"
                            class="w-full h-auto object-cover mb-2">
                    </a>
                @else
                    <img src="{{ $panel['image'] }}" alt="{{ $panel['alt'] ?? '' }}"
                        class="w-full h-auto object-cover mb-2">
                @endif
            @endif

        </div>
    @endforeach
</div>