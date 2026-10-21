@props(['submitAction', 'submitLabel', 'loadingLabel'])

@php($hasError = $errors->has('member_id_no'))

<form wire:submit="{{ $submitAction }}" class="space-y-5">
    <div>
        <x-form.label for="member_id_no">PSA ID</x-form.label>

        <input id="member_id_no" type="text" wire:model="member_id_no" autocomplete="off" autocapitalize="characters"
            inputmode="numeric" placeholder="Enter your PSA ID" minlength="4" maxlength="4"
            aria-describedby="member-id-help{{ $hasError ? ' member-id-error' : '' }}"
            @if ($hasError) aria-invalid="true" @endif
            class="block w-full rounded-lg border bg-slate-50 px-3.5 py-3 text-base font-medium text-slate-900
                   placeholder:font-normal placeholder:text-slate-400 transition duration-200
                   focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100
                   {{ $hasError ? 'border-red-400 focus:border-red-500' : 'border-slate-200 focus:border-blue-600' }}">

        <p id="member-id-help" class="sr-only">Enter your four-character PSA ID.</p>

        @if ($hasError)
            <div id="member-id-error" role="alert"
                class="mt-3 flex items-start gap-2 rounded-lg border border-red-100 bg-red-50 p-3 text-sm text-red-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                </svg>
                <span>{{ $errors->first('member_id_no') }}</span>
            </div>
        @endif
    </div>

    <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submitAction }}"
        class="flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white
               shadow-sm transition duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200
               disabled:cursor-wait disabled:opacity-60">
        <span wire:loading.remove wire:target="{{ $submitAction }}">{{ $submitLabel }}</span>
        <span wire:loading wire:target="{{ $submitAction }}">{{ $loadingLabel }}</span>
    </button>
</form>
