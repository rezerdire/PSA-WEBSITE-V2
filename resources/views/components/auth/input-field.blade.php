@props(['name', 'label', 'type' => 'text', 'icon', 'placeholder' => '', 'autocomplete' => null])

<div class="w-full min-w-0">
    <label for="{{ $name }}" class="mb-2 block text-sm font-semibold text-slate-700">
        {{ $label }}
    </label>

    <div class="relative w-full min-w-0">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                {!! $icon !!}
            </svg>
        </div>

        <input
            {{ $attributes->merge([
                'id' => $name,
                'type' => $type,
                'placeholder' => $placeholder,
                'autocomplete' => $autocomplete,
                'class' => 'block w-full min-w-0 appearance-none rounded-xl border py-3.5 pl-11 pr-4 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 sm:text-sm ' .
                    ($errors->has($name)
                        ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-500/10'
                        : 'border-slate-300 bg-slate-50 hover:border-slate-400 focus:border-blue-600 focus:ring-blue-600/10'),
            ]) }}
        />
    </div>

    @error($name)
        <p class="mt-2 flex items-start gap-1.5 text-xs font-medium text-red-600">
            <span aria-hidden="true">•</span>
            <span class="break-words">{{ $message }}</span>
        </p>
    @enderror
</div>