<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Member;
use App\Models\MemberPicture;

new class extends Component {
    use WithFileUploads;

    public bool $mustChangePassword = false;
    public string $activeTab = 'dashboard'; 

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public bool $showCurrentPassword = false;
    public bool $showNewPassword = false;
    public bool $showConfirmPassword = false;

    public string $mem_email_address = '';
    public string $mem_mobile_no1 = '';
    public string $mem_home_address = '';

    public bool $editMode = false;

    public $newPicture = null; // temp upload

    public function mount(): void
    {
        $account = Auth::user();
        $this->mustChangePassword = (bool) $account->must_change_password;

        $member = $this->member();
        if ($member) {
            $this->mem_email_address = (string) $member->mem_email_address;
            $this->mem_mobile_no1 = (string) $member->mem_mobile_no1;
            $this->mem_home_address = (string) $member->mem_home_address;
        }
    }

    private function member(): ?Member
    {
        return Member::with(['chapter', 'hospitals', 'picture'])
            ->where('member_id_no', Auth::user()->member_id_no)
            ->first();
    }

    private function primaryHospital(Member $member): ?\App\Models\MemberHospital
    {
        return $member->hospitals->firstWhere('hosp_primary', true) ?? $member->hospitals->first();
    }

    public function getPictureUrlProperty(): ?string
    {
        $member = $this->member();
        $path = $member?->picture?->mem_pic;

        return $path ? asset($path) : null;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->editMode = false;
        $this->resetErrorBag();
    }

    public function updatePassword(): void
    {
        $account = Auth::user();

        $this->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        if (!Hash::check($this->current_password, $account->password)) {
            $this->addError('current_password', 'Your current/temporary password is incorrect.');
            return;
        }

        // Account model casts 'password' => 'hashed' — pass plain value, do NOT Hash::make().
        $account->update([
            'password' => $this->new_password,
            'must_change_password' => false,
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->mustChangePassword = false;

        session()->flash('success', 'Password updated successfully.');
    }

    public function toggleEdit(): void
    {
        $this->editMode = !$this->editMode;
    }

    public function saveProfile(): void
    {
        $member = $this->member();

        if (!$member) {
            $this->addError('mem_email_address', 'Member record not found.');
            return;
        }

        $validated = $this->validate([
            'mem_email_address' => ['required', 'email', 'max:255'],
            'mem_mobile_no1' => ['nullable', 'string', 'max:20'],
            'mem_home_address' => ['nullable', 'string', 'max:255'],
        ]);

        $member->update($validated);

        $this->editMode = false;
        session()->flash('success', 'Profile updated successfully.');
    }
    public function updatePicture(): void
    {
        $this->validate([
            'newPicture' => ['required', 'image', 'max:5120'], // 5MB
        ]);

        $member = $this->member();

        if (!$member) {
            $this->addError('newPicture', 'Member record not found.');
            return;
        }

        // Append-safe: unique filename per upload, never overwrites an existing file.
        $filename = $member->member_id_no . '_' . now()->timestamp . '.' . $this->newPicture->getClientOriginalExtension();
        $destination = public_path('member-pics');

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $this->newPicture->move($destination, $filename);

        MemberPicture::updateOrCreate(['psa_id' => $member->member_id_no], ['mem_pic' => 'member-pics/' . $filename]);

        $this->reset('newPicture');
        session()->flash('success', 'Profile picture updated successfully.');
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirect(route('login'), navigate: true);
    }
};
?>

<div class="min-h-screen w-full max-w-[100vw] overflow-x-hidden bg-slate-50" x-data="{ sidebarOpen: false }">

    @if ($mustChangePassword)
        {{-- FORCED PASSWORD RESET — blocks everything else --}}
        <div class="flex min-h-screen w-full items-center justify-center px-4 py-8"
            style="padding-top: max(2rem, env(safe-area-inset-top)); padding-bottom: max(2rem, env(safe-area-inset-bottom));">
            <div
                class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 sm:rounded-3xl">
                <div class="relative overflow-hidden bg-blue-500 px-5 py-7 sm:px-8 sm:py-10">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-blue-200">Security</p>
                    <h1 class="text-xl font-bold tracking-tight text-white sm:text-3xl">Set a Permanent Password</h1>
                    <p class="mt-3 text-sm leading-6 text-blue-100">
                        You're using a temporary password. Please set a new one before continuing.
                    </p>
                </div>

                <form wire:submit="updatePassword" class="p-4 sm:p-8">
                    @error('current_password')
                        <x-account.activate-component.alert type="error" icon="warning" title="We couldn't continue"
                            class="mb-6">
                            {{ $message }}
                        </x-account.activate-component.alert>
                    @enderror

                    <div class="space-y-4 sm:space-y-5" x-data="{ show1: false, show2: false, show3: false }">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Temporary / current
                                password</label>
                            <div class="relative">
                                <input :type="show1 ? 'text' : 'password'" wire:model="current_password"
                                    class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 pr-12 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                <button type="button" @click="show1 = !show1"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <svg x-show="!show1" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="show1" x-cloak class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">New password</label>
                            <div class="relative">
                                <input :type="show2 ? 'text' : 'password'" wire:model="new_password"
                                    class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 pr-12 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                <button type="button" @click="show2 = !show2"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <svg x-show="!show2" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="show2" x-cloak class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('new_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Confirm new password</label>
                            <div class="relative">
                                <input :type="show3 ? 'text' : 'password'" wire:model="new_password_confirmation"
                                    class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 pr-12 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                <button type="button" @click="show3 = !show3"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <svg x-show="!show3" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <svg x-show="show3" x-cloak class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword"
                            class="flex min-h-[3rem] w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                            <span wire:loading wire:target="updatePassword">Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        {{-- MAIN APP SHELL --}}
        @php
            $member = $this->member();
            $hospital = $member ? $this->primaryHospital($member) : null;
        @endphp

        <div class="flex min-h-screen w-full">

            {{-- Sidebar --}}
            <aside
                class="fixed inset-y-0 left-0 z-30 flex w-72 max-w-[85vw] flex-col border-r border-slate-200 bg-blue-700 transition-transform duration-300 ease-in-out lg:w-64 lg:max-w-none lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);">

                <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-white/10 px-5">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-sm font-bold text-[#000066]">
                            PSA</div>
                        <span class="text-sm font-bold text-white">Member Portal</span>
                    </div>

                    <button type="button" @click="sidebarOpen = false"
                        class="rounded-lg p-1.5 text-blue-100/70 hover:bg-white/10 hover:text-white lg:hidden">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
                    <button type="button" wire:click="setTab('dashboard')" @click="sidebarOpen = false"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ $activeTab === 'dashboard' ? 'bg-white/10 text-white' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </button>

                    <button type="button" wire:click="setTab('settings')" @click="sidebarOpen = false"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ $activeTab === 'settings' ? 'bg-white/10 text-white' : 'text-blue-100/70 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Account Settings
                    </button>
                </nav>

                <div class="shrink-0 border-t border-white/10 p-3">
                    <button type="button" wire:click="logout"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-blue-100/70 transition hover:bg-white/5 hover:text-white">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 5v1a3 3 0 01-3 3H6a3 3 0 01-3-3V6a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign out
                    </button>
                </div>
            </aside>

            {{-- Mobile overlay --}}
            <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
                class="fixed inset-0 z-20 bg-slate-900/40 lg:hidden"></div>

            {{-- Main --}}
            <div class="flex w-full flex-1 flex-col lg:pl-64">

                {{-- Topbar --}}
                <header
                    class="sticky top-0 z-10 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-3 sm:px-6"
                    style="padding-top: env(safe-area-inset-top);">
                    <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                        <button type="button" @click="sidebarOpen = true"
                            class="shrink-0 rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="truncate text-sm font-bold text-slate-800 sm:text-base">
                            {{ $activeTab === 'dashboard' ? 'Dashboard' : 'Account Settings' }}
                        </h1>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        @if ($this->pictureUrl)
                            <img src="{{ $this->pictureUrl }}" alt="Profile"
                                class="h-9 w-9 rounded-xl object-cover ring-1 ring-slate-200">
                        @else
                            <x-account.activate-component.avatar :first="$member?->mem_first_name" :last="$member?->mem_last_name" size="9" />
                        @endif
                    </div>
                </header>

                {{-- Content --}}
                <main class="flex-1 px-3 py-5 sm:px-6 sm:py-6 lg:px-8"
                    style="padding-bottom: max(1.25rem, env(safe-area-inset-bottom));">

                    @if (session('success'))
                        <x-account.activate-component.alert type="success" icon="check" title="Success"
                            class="mb-6">
                            {{ session('success') }}
                        </x-account.activate-component.alert>
                    @endif

                    @if ($activeTab === 'dashboard')
                        {{-- MODULE 1: EMPTY DASHBOARD / WELCOME --}}
                        <div class="mx-auto max-w-5xl">
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="relative overflow-hidden bg-blue-500 px-5 py-8 sm:px-10 sm:py-14">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-blue-200">PSA
                                        Member Portal</p>
                                    <h2 class="text-xl font-bold tracking-tight text-white sm:text-3xl">
                                        Welcome, {{ $member?->mem_first_name ?? 'Member' }}!
                                    </h2>
                                    <p class="mt-3 max-w-xl text-sm leading-6 text-blue-100 sm:text-base">
                                        Glad to have you here. Use the sidebar to manage your account settings.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:mt-6 sm:grid-cols-3 sm:gap-4">
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">PSA ID</p>
                                    <p class="mt-1 text-lg font-bold text-slate-800">
                                        {{ $member?->member_id_no ?? '—' }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Chapter</p>
                                    <p class="mt-1 text-lg font-bold text-slate-800">
                                        {{ $member?->chapter?->psa_chapter_desc ?? '—' }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</p>
                                    <p class="mt-1 text-lg font-bold text-slate-800">{{ $member?->mem_stat ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- MODULE 2: ACCOUNT SETTINGS --}}
                        <div class="mx-auto max-w-3xl space-y-4 sm:space-y-6">

                            {{-- Profile picture --}}
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-100 px-4 py-4 sm:px-8">
                                    <h2 class="text-sm font-bold text-slate-900">Profile Picture</h2>
                                    <p class="mt-0.5 text-xs text-slate-500">Shown across your member portal.</p>
                                </div>

                                <div class="p-4 sm:p-8">
                                    @error('newPicture')
                                        <x-account.activate-component.alert type="error" icon="warning"
                                            title="Couldn't update picture" class="mb-6">
                                            {{ $message }}
                                        </x-account.activate-component.alert>
                                    @enderror

                                    <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-center">
                                        @if ($newPicture)
                                            <img src="{{ $newPicture->temporaryUrl() }}" alt="Preview"
                                                class="h-24 w-24 rounded-2xl object-cover ring-2 ring-blue-100">
                                        @elseif ($this->pictureUrl)
                                            <img src="{{ $this->pictureUrl }}" alt="Profile"
                                                class="h-24 w-24 rounded-2xl object-cover ring-2 ring-slate-100">
                                        @else
                                            <x-account.activate-component.avatar :first="$member?->mem_first_name" :last="$member?->mem_last_name"
                                                size="24" class="!text-2xl" />
                                        @endif

                                        <div class="flex w-full flex-col gap-2 sm:w-auto">
                                            <label
                                                class="flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-300">
                                                <span wire:loading.remove wire:target="newPicture">Choose Photo</span>
                                                <span wire:loading wire:target="newPicture">Uploading...</span>
                                                <input type="file" wire:model="newPicture" accept="image/*"
                                                    class="hidden">
                                            </label>

                                            @if ($newPicture)
                                                <button type="button" wire:click="updatePicture"
                                                    wire:loading.attr="disabled" wire:target="updatePicture"
                                                    class="rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60">
                                                    <span wire:loading.remove wire:target="updatePicture">Save
                                                        Photo</span>
                                                    <span wire:loading wire:target="updatePicture">Saving...</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Profile info --}}
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div
                                    class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-8">
                                    <div class="min-w-0">
                                        <h2 class="text-sm font-bold text-slate-900">Your Information</h2>
                                        <p class="mt-0.5 text-xs text-slate-500">Details on file with PSA.</p>
                                    </div>

                                    <button type="button" wire:click="toggleEdit"
                                        class="shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-300">
                                        {{ $editMode ? 'Cancel' : 'Edit' }}
                                    </button>
                                </div>

                                <div class="p-4 sm:p-8">
                                    @error('mem_email_address')
                                        <x-account.activate-component.alert type="error" icon="warning"
                                            title="We couldn't save your changes" class="mb-6">
                                            {{ $message }}
                                        </x-account.activate-component.alert>
                                    @enderror

                                    @if (!$editMode)
                                        <div class="space-y-3 sm:space-y-4">
                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <p
                                                        class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                        PSA ID</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800">
                                                        {{ $member?->member_id_no ?: '—' }}</p>
                                                </div>

                                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <p
                                                        class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                        PRC No.</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800">
                                                        {{ $member?->mem_prc_no ?: '—' }}</p>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <p
                                                        class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                        Email</p>
                                                    <p class="mt-1 break-all text-sm font-semibold text-slate-800">
                                                        {{ $member?->mem_email_address ?: '—' }}</p>
                                                </div>

                                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <p
                                                        class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                        Mobile No.</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-800">
                                                        {{ $member?->mem_mobile_no1 ?: '—' }}</p>
                                                </div>
                                            </div>

                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                    Home Address</p>
                                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                                    {{ $member?->mem_home_address ?: '—' }}</p>
                                            </div>

                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                    Chapter</p>
                                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                                    {{ $member?->chapter?->psa_chapter_desc ?: '—' }}</p>
                                            </div>

                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                                    Hospital Affiliation</p>
                                                @if ($hospital)
                                                    <p class="mt-1 text-sm font-semibold text-slate-800">
                                                        {{ $hospital->hospital }}</p>
                                                    @if ($hospital->hosp_address)
                                                        <p class="mt-0.5 text-xs text-slate-500">
                                                            {{ $hospital->hosp_address }}</p>
                                                    @endif
                                                @else
                                                    <p class="mt-1 text-sm font-semibold text-slate-800">—</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <form wire:submit="saveProfile" class="space-y-4 sm:space-y-5">
                                            <div>
                                                <label class="mb-2 block text-sm font-semibold text-slate-700">Email
                                                    address</label>
                                                <input type="email" wire:model="mem_email_address"
                                                    class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                                @error('mem_email_address')
                                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label class="mb-2 block text-sm font-semibold text-slate-700">Mobile
                                                    number</label>
                                                <input type="text" wire:model="mem_mobile_no1"
                                                    class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                            </div>

                                            <div>
                                                <label class="mb-2 block text-sm font-semibold text-slate-700">Home
                                                    address</label>
                                                <input type="text" wire:model="mem_home_address"
                                                    class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                            </div>

                                            <p class="text-xs text-slate-400">PSA ID, PRC No., Chapter, and Hospital
                                                Affiliation are official records — contact PSA Secretariat to update
                                                these.</p>

                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <button type="button" wire:click="toggleEdit"
                                                    class="order-2 flex min-h-[3rem] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-300 sm:order-1">
                                                    Cancel
                                                </button>

                                                <button type="submit" wire:loading.attr="disabled"
                                                    wire:target="saveProfile"
                                                    class="order-1 flex min-h-[3rem] items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60 sm:order-2">
                                                    <span wire:loading.remove wire:target="saveProfile">Save
                                                        Changes</span>
                                                    <span wire:loading wire:target="saveProfile">Saving...</span>
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Change password --}}
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-100 px-4 py-4 sm:px-8">
                                    <h2 class="text-sm font-bold text-slate-900">Change Password</h2>
                                    <p class="mt-0.5 text-xs text-slate-500">Update your account password.</p>
                                </div>

                                <form wire:submit="updatePassword" class="p-4 sm:p-8">
                                    @error('current_password')
                                        <x-account.activate-component.alert type="error" icon="warning"
                                            title="We couldn't continue" class="mb-6">
                                            {{ $message }}
                                        </x-account.activate-component.alert>
                                    @enderror

                                    <div class="space-y-4 sm:space-y-5" x-data="{ show1: false, show2: false, show3: false }">
                                        <div>
                                            <label class="mb-2 block text-sm font-semibold text-slate-700">Current
                                                password</label>
                                            <div class="relative">
                                                <input :type="show1 ? 'text' : 'password'"
                                                    wire:model="current_password"
                                                    class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 pr-12 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                                <button type="button" @click="show1 = !show1"
                                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                                    <svg x-show="!show1" class="h-5 w-5" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.8"
                                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <svg x-show="show1" x-cloak class="h-5 w-5" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.8"
                                                            d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                    </svg>
                                                </button>
                                            </div>
                                            @error('current_password')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">
                                            <div>
                                                <label class="mb-2 block text-sm font-semibold text-slate-700">New
                                                    password</label>
                                                <div class="relative">
                                                    <input :type="show2 ? 'text' : 'password'"
                                                        wire:model="new_password"
                                                        class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 pr-12 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                                    <button type="button" @click="show2 = !show2"
                                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                                        <svg x-show="!show2" class="h-5 w-5" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <svg x-show="show2" x-cloak class="h-5 w-5" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                @error('new_password')
                                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label class="mb-2 block text-sm font-semibold text-slate-700">Confirm
                                                    new password</label>
                                                <div class="relative">
                                                    <input :type="show3 ? 'text' : 'password'"
                                                        wire:model="new_password_confirmation"
                                                        class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 pr-12 text-sm text-slate-900 shadow-sm outline-none transition hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-600/10 sm:h-14">
                                                    <button type="button" @click="show3 = !show3"
                                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                                        <svg x-show="!show3" class="h-5 w-5" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <svg x-show="show3" x-cloak class="h-5 w-5" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="1.8"
                                                                d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" wire:loading.attr="disabled"
                                            wire:target="updatePassword"
                                            class="flex min-h-[3rem] w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:px-8">
                                            <span wire:loading.remove wire:target="updatePassword">Update
                                                Password</span>
                                            <span wire:loading wire:target="updatePassword">Updating...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    @endif
</div>
