<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Member;
use App\Models\Account;
use App\Mail\TemporaryPasswordMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

new class extends Component {
    public string $lastName = '';
    public ?string $selectedMemberId = null;
    public bool $showEmailConfirm = false;

    #[Computed]
    public function results()
    {
        if (strlen(trim($this->lastName)) < 2) {
            return collect();
        }

        return Member::where('mem_last_name', 'like', '%' . trim($this->lastName) . '%')
            ->orderBy('mem_last_name')
            ->get(['member_id_no', 'mem_last_name', 'mem_first_name', 'mem_middle_name', 'mem_email_address']);
    }

    #[Computed]
    public function selectedMember()
    {
        return $this->selectedMemberId ? Member::where('member_id_no', $this->selectedMemberId)->first() : null;
    }

    #[Computed]
    public function maskedEmail()
    {
        return $this->maskEmail($this->selectedMember?->mem_email_address);
    }

    private function maskEmail(?string $email): string
    {
        if (empty($email) || !str_contains($email, '@')) {
            return '';
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = substr($local, -max(1, intdiv(strlen($local), 2)));

        return '***' . $visible . '@' . $domain;
    }

    /**
     * Shared eligibility check used by both selectMember() and confirmEmail(),
     * replacing the previously duplicated "already has account / no email" checks.
     * Returns the Member on success, or null after setting a validation error.
     */
    private function findEligibleMember(string $memberId): ?Member
    {
        $member = Member::where('member_id_no', $memberId)->first();

        if (!$member) {
            $this->addError('selectedMemberId', 'Member account could not be found.');
            return null;
        }

        if (Account::where('member_id_no', $member->member_id_no)->exists()) {
            $this->addError('selectedMemberId', 'This member already has an activated account.');
            return null;
        }

        if (empty($member->mem_email_address)) {
            $this->addError('selectedMemberId', 'No email address is registered for this member. Please contact PSA Secretariat.');
            return null;
        }

        return $member;
    }

    public function selectMember(string $memberId): void
    {
        $this->resetErrorBag();

        if (!($member = $this->findEligibleMember($memberId))) {
            return;
        }

        $this->selectedMemberId = $member->member_id_no;
        $this->showEmailConfirm = true;
    }

    public function cancelConfirm(): void
    {
        $this->showEmailConfirm = false;
        $this->selectedMemberId = null;
        $this->resetErrorBag();
    }

    public function confirmEmail(): void
    {
        $this->validate(['selectedMemberId' => ['required', 'string']]);

        if (!($member = $this->findEligibleMember($this->selectedMemberId))) {
            return;
        }

        $temporaryPassword = Str::random(12);

        // Create the account first. If email sending fails, the account still
        // exists and the user can use "forgot password" later, but we surface
        // a clear error so they know to contact support.
        $account = Account::create([
            'member_id_no' => $member->member_id_no,
            'psa_id' => $member->member_id_no,
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'is_active' => true,
        ]);

        try {
            Mail::to($member->mem_email_address)->send(new TemporaryPasswordMail($member, $temporaryPassword));
        } catch (\Throwable $e) {
            Log::error('Failed to send temporary password email', [
                'member_id_no' => $member->member_id_no,
                'error' => $e->getMessage(),
            ]);

            $this->addError('selectedMemberId', 'Your account was created, but we could not send the email with your temporary password. Please contact PSA Secretariat for assistance.');
            return;
        }

        $this->showEmailConfirm = false;
        $this->selectedMemberId = null;
        $this->lastName = '';

        session()->flash('success', 'Your account has been activated. A temporary password has been sent to your registered email address.');
    }
};
?>

<div class="min-h-screen w-full overflow-x-hidden bg-slate-50 pt-16">
    <div class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Back to login --}}
        <div class="mb-6">
            <a href="{{ route('login') }}"
                class="group inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-blue-700">
                <x-account.activate-component.icon name="back"
                    class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" />
                Back to Sign in
            </a>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 sm:rounded-3xl">

            {{-- Header --}}
            <div class="relative overflow-hidden bg-blue-500 px-5 py-8 sm:px-8 sm:py-10">
                <div class="relative max-w-2xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-blue-200">PSA Member Account
                    </p>
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Find & Activate Your Account
                    </h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-blue-100 sm:text-base">
                        Already a PSA member? Find your membership record and activate
                        your online account using the email address registered with PSA.
                    </p>
                </div>
            </div>

            <div class="p-5 sm:p-8 lg:p-10">

                {{-- Steps --}}
                <div class="mb-8 grid gap-3 sm:grid-cols-3">
                    <x-account.activate-component.step-indicator :number="1" title="Find your record"
                        description="Search using your last name." :active="true" />
                    <x-account.activate-component.step-indicator :number="2" title="Confirm your email"
                        description="Verify the masked email on file." />
                    <x-account.activate-component.step-indicator :number="3" title="Check your email"
                        description="Receive your temporary password." />
                </div>

                @if (session('success'))
                    <x-account.activate-component.alert type="success" icon="check"
                        title="Account activation successful" class="mb-8">
                        {{ session('success') }}
                        <a href="{{ route('login') }}"
                            class="mt-3 flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-900 hover:underline">
                            Continue to Sign in
                            <x-account.activate-component.icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </x-account.activate-component.alert>
                @endif

                @error('selectedMemberId')
                    <x-account.activate-component.alert type="error" icon="warning" title="We couldn't continue"
                        class="mb-6">
                        {{ $message }}
                    </x-account.activate-component.alert>
                @enderror

                @if (!$showEmailConfirm)
                    {{-- Search Section --}}
                    <div class="mx-auto max-w-3xl">
                        <div class="mb-6">
                            <h2 class="text-lg font-bold text-slate-900">Search for your membership</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Enter your last name exactly or partially as it appears in your PSA membership record.
                            </p>
                        </div>

                        <div class="relative">
                            <label for="lastName" class="mb-2 block text-sm font-semibold text-slate-700">Last
                                name</label>

                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <x-account.activate-component.icon name="search" class="h-5 w-5 text-slate-400"
                                        strokeWidth="1.8" />
                                </div>

                                <input id="lastName" type="text" wire:model.live.debounce.400ms="lastName"
                                    placeholder="Enter your last name" autocomplete="off" autofocus
                                    class="h-14 w-full rounded-xl border border-slate-300 bg-white pl-12 pr-12 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10">

                                <div wire:loading wire:target="lastName"
                                    class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <x-account.activate-component.icon name="spinner" class="h-5 w-5 text-blue-600" />
                                </div>
                            </div>

                            <p class="mt-2 text-xs text-slate-400">Enter at least 2 characters to begin searching.</p>
                        </div>

                        {{-- Search Results --}}
                        <div class="mt-8">
                            @if (strlen(trim($lastName)) < 2)
                                <x-account.activate-component.empty-state icon="search"
                                    title="Search for your PSA membership" :dashed="true">
                                    Your matching membership records will appear here.
                                </x-account.activate-component.empty-state>
                            @elseif ($this->results->isEmpty())
                                <x-account.activate-component.empty-state icon="no-results"
                                    title="No matching members found">
                                    We couldn't find a membership record matching
                                    <span class="font-medium text-slate-500">"{{ $lastName }}"</span>.
                                    Try checking the spelling.
                                </x-account.activate-component.empty-state>
                            @else
                                <div class="mb-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Select your membership</p>
                                        <p class="mt-0.5 text-xs text-slate-400">Choose the record that belongs to you.
                                        </p>
                                    </div>

                                    <span
                                        class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        {{ $this->results->count() }}
                                        {{ Str::plural('match', $this->results->count()) }}
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    @foreach ($this->results as $member)
                                        <x-account.activate-component.member-row :member="$member" />
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif ($this->selectedMember)
                    {{-- Email Confirmation --}}
                    <div class="mx-auto max-w-2xl">
                        <div class="mb-8 flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-700 text-sm font-bold text-white">
                                2</div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Confirm your email address</p>
                                <p class="text-xs text-slate-400">Make sure this email belongs to you.</p>
                            </div>
                        </div>

                        <div class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                            <div class="flex items-center gap-4">
                                <x-account.activate-component.avatar :first="$this->selectedMember->mem_first_name" :last="$this->selectedMember->mem_last_name"
                                    size="12" />

                                <div class="min-w-0">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Selected
                                        member</p>
                                    <p class="mt-1 truncate text-sm font-bold text-slate-800">
                                        {{ $this->selectedMember->mem_last_name }},
                                        {{ $this->selectedMember->mem_first_name }}
                                        {{ $this->selectedMember->mem_middle_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        PSA ID: <span
                                            class="font-medium text-slate-500">{{ $this->selectedMember->member_id_no }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-5 sm:p-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-blue-700 shadow-sm ring-1 ring-blue-100">
                                    <x-account.activate-component.icon name="mail" strokeWidth="1.8" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-800">Is this your registered email?</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Your temporary password will be sent to this email address.
                                    </p>

                                    <div class="mt-4 rounded-xl border border-white bg-white px-4 py-3.5 shadow-sm">
                                        <p
                                            class="break-all font-mono text-sm font-bold tracking-tight text-slate-800 sm:text-base">
                                            {{ $this->maskedEmail }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <x-account.activate-component.alert type="warning" icon="warning-thin" title="Important"
                            class="mt-4">
                            If you do not recognize this email address, do not continue.
                            Please contact the PSA Secretariat to update your membership information.
                        </x-account.activate-component.alert>

                        <p class="mt-5 text-center text-xs leading-5 text-slate-500">
                            Not your email?
                            <a href="mailto:psainc_sec@yahoo.com"
                                class="font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                                Contact PSA Secretariat
                            </a>
                        </p>

                        <div class="mt-7 grid gap-3 sm:grid-cols-2">
                            <button type="button" wire:click="cancelConfirm" wire:loading.attr="disabled"
                                wire:target="confirmEmail"
                                class="flex min-h-[3rem] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-300 disabled:cursor-not-allowed disabled:opacity-60">
                                <x-account.activate-component.icon name="arrow-left" class="mr-2 h-4 w-4" />
                                Search again
                            </button>

                            <button type="button" wire:click="confirmEmail" wire:loading.attr="disabled"
                                wire:target="confirmEmail"
                                class="flex min-h-[3rem] items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition duration-200 hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-blue-700/30 focus:outline-none focus:ring-4 focus:ring-blue-600/20 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0">
                                <span wire:loading wire:target="confirmEmail">
                                    <x-account.activate-component.icon name="spinner" class="h-5 w-5" />
                                </span>
                                <span wire:loading.remove wire:target="confirmEmail">Send Temporary Password</span>
                                <span wire:loading wire:target="confirmEmail">Activating...</span>
                            </button>
                        </div>
                    </div>
                @endif

                @if (!$showEmailConfirm)
                    <div class="mx-auto mt-10 max-w-3xl border-t border-slate-100 pt-7">
                        <div
                            class="flex flex-col gap-4 rounded-2xl bg-slate-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm ring-1 ring-slate-200">
                                    <x-account.activate-component.icon name="help" class="h-4 w-4"
                                        strokeWidth="1.8" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Can't find your name or email?</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Please contact us at
                                        psainc_sec@yahoo.com.</p>
                                </div>
                            </div>

                            <a href="mailto:psainc_sec@yahoo.com"
                                class="shrink-0 text-sm font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                                Contact Secretariat
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-4 text-center sm:px-8">
                <p class="text-xs leading-5 text-slate-400">
                    Your membership information is protected.
                    Only use this page to activate your own PSA account.
                </p>
            </div>
        </div>
    </div>
</div>
