@props(['first' => '', 'last' => '', 'size' => 11])

<div {{ $attributes->merge(['class' => "flex h-{$size} w-{$size} shrink-0 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700 ring-1 ring-blue-100"]) }}>
    {{ strtoupper(substr($first, 0, 1)) }}{{ strtoupper(substr($last, 0, 1)) }}
</div>
