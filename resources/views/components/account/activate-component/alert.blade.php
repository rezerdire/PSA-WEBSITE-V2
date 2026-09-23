@props(['type' => 'error', 'icon' => 'warning', 'title' => null])

@php
    $theme = [
        'success' => [
            'border' => 'border-emerald-200',
            'bg' => 'bg-emerald-50',
            'iconBg' => 'bg-emerald-100',
            'iconColor' => 'text-emerald-600',
            'title' => 'text-emerald-900',
            'text' => 'text-emerald-800',
        ],
        'error' => [
            'border' => 'border-red-200',
            'bg' => 'bg-red-50',
            'iconBg' => 'bg-red-100',
            'iconColor' => 'text-red-600',
            'title' => 'text-red-800',
            'text' => 'text-red-700',
        ],
        'warning' => [
            'border' => 'border-amber-200',
            'bg' => 'bg-amber-50',
            'iconBg' => null,
            'iconColor' => 'text-amber-600',
            'title' => 'text-amber-800',
            'text' => 'text-amber-700',
        ],
    ][$type];
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl border {$theme['border']} {$theme['bg']} p-4 sm:p-5"]) }}>
    <div class="flex items-start gap-4">
        @if ($theme['iconBg'])
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $theme['iconBg'] }}">
                <x-account.activate-component.icon :name="$icon" class="h-5 w-5 {{ $theme['iconColor'] }}" />
            </div>
        @else
            <x-account.activate-component.icon :name="$icon"
                class="mt-0.5 h-5 w-5 shrink-0 {{ $theme['iconColor'] }}" />
        @endif

        <div class="min-w-0">
            @if ($title)
                <h3 class="text-sm font-bold {{ $theme['title'] }}">{{ $title }}</h3>
            @endif

            <div class="mt-1 text-sm leading-6 {{ $theme['text'] }}">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
