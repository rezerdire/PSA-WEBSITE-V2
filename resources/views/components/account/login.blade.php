<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

new class extends Component {
    public string $psa_id = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'psa_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt(['psa_id' => $this->psa_id, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'psa_id' => 'The PSA ID or password you entered is incorrect.',
            ]);
        }

        request()->session()->regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>

<div class="w-full max-w-[100vw] overflow-x-hidden bg-slate-50 pt-16">
    <div class="flex w-full flex-col lg:flex-row">

        <div class="flex w-full flex-1 items-center justify-center px-4 py-8 sm:px-8 sm:py-10 lg:w-1/2 xl:w-[45%]"
            style="padding-top: max(2rem, env(safe-area-inset-top)); padding-bottom: max(2rem, env(safe-area-inset-bottom));">

            <div class="w-full max-w-md">

                <div class="mb-6 sm:mb-8 text-center justify-center">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        Sign in
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Sign in to access your PSA member account.
                    </p>
                </div>

                <form wire:submit="login"
                    class="w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-xl shadow-slate-200/60 xs:p-5 sm:rounded-3xl sm:p-8">

                    @if ($errors->any())
                        <div class="mb-6 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4" role="alert">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15a2 2 0 001.71 3h17.64a2 2 0 001.71-3l-8.82-15a2 2 0 00-3.42 0z" />
                            </svg>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-red-800">
                                    Unable to sign in
                                </p>

                                <p class="mt-1 break-words text-xs leading-5 text-red-700">
                                    {{ $errors->first() }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-5">

                        <x-auth.input-field name="psa_id" label="PSA ID" wire:model="psa_id"
                            placeholder="Enter your PSA ID" autocomplete="username" required autofocus
                            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a3 3 0 11-6 0 3 3 0 016 0zM4 21a8 8 0 0116 0M19 8h2m-1-1v2" />' />

                        <x-auth.password-field wire:model="password" required />

                        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3">

                            <label class="-my-1 flex cursor-pointer items-center gap-2.5 py-1">
                                <input wire:model="remember" type="checkbox"
                                    class="h-5 w-5 shrink-0 rounded border-slate-300 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-600/20" />

                                <span class="text-sm text-slate-600">
                                    Remember me
                                </span>
                            </label>

                            <a href="#"
                                class="-my-1 py-1 text-sm font-semibold text-blue-600 transition hover:text-blue-700 hover:underline">
                                Forgot password?
                            </a>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="login"
                            class="group flex min-h-[3rem] w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition duration-200 hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-blue-700/30 focus:outline-none focus:ring-4 focus:ring-blue-600/20 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0">

                            <svg wire:loading wire:target="login" class="h-5 w-5 animate-spin" fill="none"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>

                            <span wire:loading.remove wire:target="login">
                                Sign in
                            </span>

                            <span wire:loading wire:target="login">
                                Signing in...
                            </span>

                            <svg wire:loading.remove wire:target="login"
                                class="h-4 w-4 transition-transform duration-200 group-hover:transz late-x-0.5"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 12h14m-6-6l6 6-6 6" />
                            </svg>
                        </button>


                        <a href="{{ route('activate-account') }}"
                            class="group flex min-h-[3rem] w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition duration-200 hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-blue-700/30 focus:outline-none focus:ring-4 focus:ring-blue-600/20 active:translate-y-0">
                            Find/Activate your PSA Account
                        </a>

                    </div>

                    <div class="mt-6 flex items-start gap-3 rounded-xl bg-slate-50 p-4">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 3l7 3v5c0 4.5-3 8.1-7 10-4-1.9-7-5.5-7-10V6l7-3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4" />
                        </svg>

                        <p class="text-xs leading-5 text-slate-500">
                            Your account information is protected. Never share your
                            PSA ID or password with anyone.
                        </p>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>
