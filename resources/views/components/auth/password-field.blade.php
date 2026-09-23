@props(['name' => 'password', 'label' => 'Password', 'placeholder' => 'Enter your password'])

<div class="w-full min-w-0" x-data="{ showPassword: false }">
    <div class="mb-2 flex items-center justify-between">
        <label for="{{ $name }}" class="block text-sm font-semibold text-slate-700">
            {{ $label }}
        </label>
    </div>

    <div class="relative w-full min-w-0">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M16.5 10V7a4.5 4.5 0 00-9 0v3m-1.5 0h12a1.5 1.5 0 011.5 1.5v8A1.5 1.5 0 0118 21H6a1.5 1.5 0 01-1.5-1.5v-8A1.5 1.5 0 016 10z" />
            </svg>
        </div>

        <input
            {{ $attributes->merge([
                'id' => $name,
                'x-bind:type' => "showPassword ? 'text' : 'password'",
                'autocomplete' => 'current-password',
                'placeholder' => $placeholder,
                'class' => 'block w-full min-w-0 appearance-none rounded-xl border py-3.5 pl-11 pr-12 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 sm:text-sm ' .
                    ($errors->has($name)
                        ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-500/10'
                        : 'border-slate-300 bg-slate-50 hover:border-slate-400 focus:border-blue-600 focus:ring-blue-600/10'),
            ]) }}
        />

        <button type="button" @click="showPassword = !showPassword"
            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-600"
            :aria-label="showPassword ? 'Hide password' : 'Show password'" :aria-pressed="showPassword">

            <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" />
                <circle cx="12" cy="12" r="2.5" stroke-width="1.8" />
            </svg>

            <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.2A9.8 9.8 0 0112 5c6 0 9.5 7 9.5 7a16.7 16.7 0 01-3.2 3.9M6.3 6.3C3.8 8.1 2.5 12 2.5 12s3.5 6 9.5 6c1.2 0 2.3-.2 3.3-.6" />
            </svg>
        </button>
    </div>

    @error($name)
        <p class="mt-2 flex items-start gap-1.5 text-xs font-medium text-red-600">
            <span aria-hidden="true">•</span>
            <span class="break-words">{{ $message }}</span>
        </p>
    @enderror
</div>