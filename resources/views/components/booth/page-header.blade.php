@props(['eyebrow', 'title', 'description' => null])

<div
    class="mx-auto mb-8 w-full max-w-4xl overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500">
    <div class="px-6 py-7 sm:px-8 lg:px-10">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-100">
            {{ $eyebrow }}
        </p>

        <h1 class="mt-1 text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl">
            {{ $title }}
        </h1>

        @if ($description)
            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                {{ $description }}
            </p>
        @endif

        @if (trim((string) $slot) !== '')
            <div class="mt-4 flex flex-wrap items-center gap-2">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
