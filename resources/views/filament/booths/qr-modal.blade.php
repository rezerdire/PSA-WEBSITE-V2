<div class="flex flex-col items-center gap-4">
    <div class="rounded-lg bg-white p-3">
        {!! $svg !!}
    </div>

    <p class="text-sm text-gray-500 break-all text-center">{{ $url }}</p>

    <a
        href="data:image/svg+xml;base64,{{ base64_encode($svg) }}"
        download="booth-{{ $code }}.svg"
        class="fi-btn fi-btn-size-md rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white"
    >
        Download QR (SVG)
    </a>
</div>