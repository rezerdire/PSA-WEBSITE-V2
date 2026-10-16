<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use App\Models\Member;
use App\Models\WorkshopRegistration;

new class extends Component {
    // Server-filled only (members). Stay empty for non-members.
    #[Locked]
    public string $psaId = '';
    #[Locked]
    public string $memType = '';

    // Auto-filled for members, typed in by non-members
    public string $lastName = '';
    public string $firstName = '';
    public string $middleName = '';
    public string $prcNumber = '';
    public string $hospital = '';

    // Contact details: typed in by everyone
    public string $email = '';
    public string $phone = '';

    // Only one workshop allowed
    public string $workshop = '';

    public bool $submitted = false;

    // Maximum registrants per workshop
    public const WORKSHOP_LIMIT = 60;

    public const WORKSHOPS = [
        'RA' => 'Regional Anesthesia Workshop (RA)',
        'POCUS' => 'POCUS',
        'AIRWAY' => 'Airway',
    ];

    // Program details shown when a workshop is selected.
    // Program rows: [topic, speaker, duration|null]
    // Station rows: [name, description|null, setup]
    // Leave 'program' and 'stations' empty to show no panel.
    public const WORKSHOP_DETAILS = [
        'RA' => [
            'title' => 'Regional Anesthesia Workshop',
            'program' => [['Opening Remarks and Introduction to the Course', 'Dr. Lily Jingco', null], ['Lecture on Updates on Interventional Pain', 'Dr. Richard Genuino', '20 minutes'], ['Demonstration of doing spine ultrasound', 'Dr. Roneil Nogoy', '20 minutes']],
            'session' => '9:00 AM – 12:00 Noon (Workshop Session)',
            'venue' => 'Executive 5',
            'stations' => [['MSK Session', 'Musculoskeletal ultrasound of shoulder, knee, hip and SI joints.', '2 stations with 1 model and 1 UTZ each station'], ['Plan A Blocks', null, '4 stations with 1 model and 1 UTZ each']],
        ],
        'POCUS' => [
            'title' => 'POCUS',
            'program' => [],
            'session' => null,
            'venue' => null,
            'stations' => [],
        ],
        'AIRWAY' => [
            'title' => 'Airway',
            'program' => [],
            'session' => null,
            'venue' => null,
            'stations' => [],
        ],
    ];

    // MEMBER TYPES:
    protected const MEM_TYPE_MAP = [
        'RM' => 'Regular Member',
        'LM' => 'Life Member',
        'TM' => 'Trainee Member',
    ];

    // Registrations per workshop, e.g. ['RA' => 12, 'POCUS' => 0, 'AIRWAY' => 5]
    #[Computed]
    public function taken(): array
    {
        $counts = [];
        foreach (array_keys(self::WORKSHOPS) as $key) {
            $counts[$key] = $this->countFor($key);
        }
        return $counts;
    }

    // protected so it can't be called from the browser
    protected function countFor(string $key): int
    {
        return WorkshopRegistration::whereJsonContains('workshops', $key)->count();
    }

    // The checker only sends the PSA ID. We look the member up again here,
    // so the autofilled values always come from the database.
    #[On('psa-id-selected')]
    public function fillFromMember(string $psaId): void
    {
        $member = Member::find($psaId); // same lookup as your event registration

        if (!$member) {
            $this->addError('psaId', 'PSA ID not found.');
            return;
        }

        $this->resetErrorBag();

        $code = strtoupper(trim($member->psa_mem_type ?? ''));

        $this->psaId = (string) $member->getKey();
        $this->lastName = (string) ($member->mem_last_name ?? '');
        $this->firstName = (string) ($member->mem_first_name ?? '');
        $this->middleName = (string) ($member->mem_middle_name ?? '');
        $this->memType = self::MEM_TYPE_MAP[$code] ?? $code; // "RM" -> "Regular Member"
        $this->prcNumber = (string) ($member->mem_prc_no ?? ''); // adjust column name
    }

    // PSA ID VERIFICATION
    public string $psaIdInput = ''; // what the user types

    public function verifyPsaId(): void
    {
        $this->psaIdInput = trim($this->psaIdInput);

        if ($this->psaIdInput === '') {
            return;
        }

        $this->fillFromMember($this->psaIdInput);
    }

    public function clearMember(): void
    {
        $this->reset(['psaId', 'memType', 'lastName', 'firstName', 'middleName', 'prcNumber']);
        $this->resetErrorBag();
    }

    public function submit(): void
    {
        $this->email = trim($this->email);
        $this->phone = trim($this->phone);

        $validated = $this->validate(
            [
                'lastName' => ['required', 'string', 'max:255'],
                'firstName' => ['required', 'string', 'max:255'],
                'middleName' => ['nullable', 'string', 'max:255'],
                'prcNumber' => ['required', 'string', 'max:50'],
                'hospital' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email:rfc', 'max:255'],
                'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
                'workshop' => ['required', 'in:' . implode(',', array_keys(self::WORKSHOPS))],
            ],
            [
                'lastName.required' => 'Please enter your last name.',
                'firstName.required' => 'Please enter your first name.',
                'prcNumber.required' => 'Please enter your PRC number.',
                'hospital.required' => 'Please enter your hospital.',
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'phone.required' => 'Please enter your phone number.',
                'phone.regex' => 'Please enter a valid phone number.',
                'workshop.required' => 'Please select a workshop.',
                'workshop.in' => 'Please select a valid workshop.',
            ],
        );

        if ($this->psaId !== '' && WorkshopRegistration::where('psa_id', $this->psaId)->exists()) {
            $this->addError('psaId', 'This PSA ID is already registered.');
            return;
        }

        if (WorkshopRegistration::where('prc_number', $validated['prcNumber'])->exists()) {
            $this->addError('prcNumber', 'This PRC number is already registered.');
            return;
        }

        // The lock makes "check the count, then insert" one step, so two people
        // grabbing the last slot at the same time can't both get in.
        try {
            $result = Cache::lock('workshop-registration', 10)->block(5, function () use ($validated) {
                if ($this->countFor($validated['workshop']) >= self::WORKSHOP_LIMIT) {
                    return 'full';
                }

                WorkshopRegistration::create([
                    'psa_id' => $this->psaId !== '' ? $this->psaId : null,
                    'mem_last_name' => $validated['lastName'],
                    'mem_first_name' => $validated['firstName'],
                    'mem_middle_name' => $validated['middleName'] ?? '',
                    'mem_type' => $this->psaId !== '' ? $this->memType : 'Non-member',
                    'prc_number' => $validated['prcNumber'],
                    'hospital' => $validated['hospital'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'workshops' => [$validated['workshop']],
                ]);

                return 'ok';
            });
        } catch (LockTimeoutException $e) {
            $this->addError('workshop', 'The system is busy. Please try again in a moment.');
            return;
        } catch (UniqueConstraintViolationException $e) {
            // Two submissions raced past the exists() check
            $this->addError('prcNumber', 'This PRC number is already registered.');
            return;
        }

        if ($result === 'full') {
            $this->addError('workshop', 'Sorry, this workshop is already full. Please choose another one.');
            $this->workshop = '';
            return;
        }

        $this->submitted = true;
    }
};
?>

<div>
    @if ($submitted)
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r from-[#000066] to-[#0000aa]"></div>
            <div class="p-8 text-center">
                <h2 class="font-bold text-lg text-[#000066]">Registration Complete</h2>
                <p class="text-sm text-gray-500 mt-2">Thank you, {{ $firstName }} {{ $lastName }}. Your workshop
                    slot has been recorded.</p>
            </div>
        </div>
    @else
        {{-- PSA ID checker (optional, members only): choosing a result fires 'psa-id-selected' --}}

        <x-event-registration.event-registration-layout>
            <livewire:psa-id-checker mode="both" heading="Find Your PSA ID" />

            <form wire:submit="submit" class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-[#000066] to-[#0000aa]"></div>
                <div class="p-6 space-y-6">

                    <div>
                        <h2 class="font-bold text-base text-[#000066]">Workshop Registration</h2>
                        <p class="text-gray-400 text-xs mt-0.5">
                            PSA members: select your PSA ID above to auto-fill your details.
                            Non-members: just fill in the fields below.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                PSA ID <span class="text-gray-400 normal-case font-normal">(members only)</span>
                            </label>
                            <div class="flex gap-2">
                                @if ($psaId)
                                    <input type="text" readonly value="{{ $psaId }}"
                                        class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2.5 text-sm  text-gray-700">
                                    <button type="button" wire:click="clearMember"
                                        class="shrink-0 text-xs font-semibold px-3 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
                                        Clear
                                    </button>
                                @else
                                    <input type="text" wire:model="psaIdInput"
                                        wire:keydown.enter.prevent="verifyPsaId"
                                        placeholder="Type PSA ID (members only)" inputmode="numeric" maxlength="4"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm  focus:outline-none focus:ring-2 focus:ring-[#000066]">
                                    <button type="button" wire:click="verifyPsaId"
                                        class="shrink-0 text-xs font-semibold px-4 rounded-lg text-white bg-[#000066] hover:opacity-90">
                                        Verify
                                    </button>
                                @endif
                            </div>
                            @error('psaId')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="lastName" placeholder="Last name"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                            @error('lastName')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="firstName" placeholder="First name"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                            @error('firstName')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Middle
                                Name</label>
                            <input type="text" wire:model="middleName" placeholder="Middle name (optional)"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                            @error('middleName')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Member Type: PSA members only, read-only, comes from the database --}}
                        @if ($psaId)
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Member
                                    Type</label>
                                <input type="text" readonly value="{{ $memType }}"
                                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2.5 text-sm text-gray-700">
                            </div>
                        @endif

                        <div class="{{ $psaId ? 'sm:col-span-2' : '' }}">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                PRC Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="prcNumber" placeholder="Enter your PRC number"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                            @error('prcNumber')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- email --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" wire:model="email" placeholder="name@example.com" autocomplete="email"
                                inputmode="email"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- phone --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" wire:model="phone" placeholder="e.g. 0917 123 4567" autocomplete="tel"
                                inputmode="tel" maxlength="20"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- hospital --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Hospital <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="hospital" placeholder="Enter your hospital / institution"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                        @error('hospital')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- workshop selection: one only --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">
                            Select Workshop <span class="text-red-500">*</span>
                            <span class="text-gray-400 normal-case font-normal">(choose one)</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach (self::WORKSHOPS as $key => $label)
                                @php
                                    $slotsTaken = $this->taken[$key] ?? 0;
                                    $slotsLeft = max(self::WORKSHOP_LIMIT - $slotsTaken, 0);
                                    $isFull = $slotsLeft === 0;
                                @endphp
                                <label wire:key="ws-{{ $key }}"
                                    class="flex items-center gap-3 border rounded-lg px-4 py-3 transition
                                    {{ $isFull ? 'opacity-60 cursor-not-allowed bg-gray-50 border-gray-200' : 'cursor-pointer' }}
                                    {{ !$isFull && $workshop === $key ? 'border-[#000066] bg-[#f4f4fb]' : '' }}
                                    {{ !$isFull && $workshop !== $key ? 'border-gray-200 hover:bg-gray-50' : '' }}">
                                    <input type="radio" wire:model.live="workshop" value="{{ $key }}"
                                        name="workshop" @disabled($isFull)
                                        class="w-4 h-4 border-gray-300 text-[#000066] focus:ring-[#000066]">
                                    <div class="min-w-0">
                                        <span
                                            class="block text-sm font-medium text-gray-700">{{ $label }}</span>
                                        @if ($isFull)
                                            <span class="block text-xs font-semibold text-red-500">Full</span>
                                        @else
                                            <span
                                                class="block text-xs {{ $slotsLeft <= 10 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
                                                {{ $slotsTaken }} / {{ self::WORKSHOP_LIMIT }} registered
                                                · {{ $slotsLeft }} {{ Str::plural('slot', $slotsLeft) }} left
                                            </span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        {{-- selected workshop details --}}
                        @php
                            $details = self::WORKSHOP_DETAILS[$workshop] ?? null;
                        @endphp

                        @if ($details && ($details['program'] || $details['stations']))
                            <div wire:key="ws-details-{{ $workshop }}" x-data x-transition
                                class="mt-4 rounded-xl border border-[#000066]/15 bg-[#f4f4fb] p-5 space-y-4">

                                <h3 class="font-bold text-sm text-[#000066] uppercase tracking-wide">
                                    {{ $details['title'] }}
                                </h3>
                                @if ($details['program'])
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                            Program</p>
                                        <div class="overflow-x-auto rounded-lg border border-[#000066]/15 bg-white">
                                            <table class="w-full text-sm text-left">
                                                <thead class="bg-[#000066] text-white text-xs uppercase tracking-wide">
                                                    <tr>
                                                        <th class="px-4 py-2.5 font-semibold">Topic</th>
                                                        <th class="px-4 py-2.5 font-semibold">Speaker</th>
                                                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">
                                                            Duration
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach ($details['program'] as [$topic, $speaker, $duration])
                                                        <tr class="odd:bg-white even:bg-gray-50">
                                                            <td class="px-4 py-2.5 text-gray-700">{{ $topic }}
                                                            </td>
                                                            <td
                                                                class="px-4 py-2.5 font-medium text-[#000066] whitespace-nowrap">
                                                                {{ $speaker }}</td>
                                                            <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">
                                                                {{ $duration ?? '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                                @if ($details['session'] || $details['venue'])
                                    <div class="rounded-lg bg-[#000066] text-white px-4 py-3 space-y-2">
                                        @if ($details['session'])
                                            <div class="flex items-center gap-2.5 text-sm font-semibold">
                                                {{-- clock icon --}}
                                                <svg class="w-4 h-4 shrink-0 text-white/70" fill="none"
                                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="9" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 7v5l3 2" />
                                                </svg>
                                                <span>{{ $details['session'] }}</span>
                                            </div>
                                        @endif

                                        @if ($details['venue'])
                                            <div class="flex items-center gap-2.5 text-sm font-semibold">
                                                {{-- location pin icon --}}
                                                <svg class="w-4 h-4 shrink-0 text-white/70" fill="none"
                                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 21s7-5.5 7-11a7 7 0 10-14 0c0 5.5 7 11 7 11z" />
                                                    <circle cx="12" cy="10" r="2.5" />
                                                </svg>
                                                <span><span class="font-normal text-white/70">Venue:</span>
                                                    {{ $details['venue'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @if ($details['stations'])
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                            Workshop Stations</p>
                                        <div class="space-y-3">
                                            @foreach ($details['stations'] as [$name, $description, $setup])
                                                <div class="text-sm">
                                                    <p class="font-semibold text-gray-800">{{ $name }}</p>
                                                    @if ($description)
                                                        <p class="text-gray-600">{{ $description }}</p>
                                                    @endif
                                                    <p class="text-gray-500 text-xs">{{ $setup }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @error('workshop')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full sm:w-auto text-sm font-semibold px-6 py-2.5 rounded-lg text-white bg-[#000066] hover:opacity-90 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="submit">Submit Registration</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </button>
                </div>
            </form>
        </x-event-registration.event-registration-layout>

    @endif
</div>
