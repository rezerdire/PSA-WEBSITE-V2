<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use App\Mail\WorkshopRegistrationConfirmation;
use App\Models\Member;
use App\Models\Workshop;
use App\Models\WorkshopRegistration;

new class extends Component {
    // Server-filled for members; generated on submit for non-members.
    #[Locked]
    public string $psaId = '';
    #[Locked]
    public string $memType = '';

    // PRC numbers are always entered by the registrant, including verified members.
    #[Locked]
    public bool $prcFromMember = false;

    // Auto-filled for members (locked), typed in by non-members
    public string $lastName = '';
    public string $firstName = '';
    public string $middleName = '';
    public string $prcNumber = '';
    public string $hospital = '';

    // Contact details: typed in by everyone
    public string $email = '';
    public string $phone = '';

    // One registrant may choose several workshops.
    public array $workshops = [];

    public bool $submitted = false;

    #[Locked]
    public bool $confirmationEmailSent = false;

    // Fallback used when a legacy workshop record has no configured capacity.
    public const WORKSHOP_LIMIT = 60;
    public const NON_MEMBER_PSA_PREFIX = 'NMW_';

    // Max PSA ID lookups per IP per minute
    protected const LOOKUP_MAX_ATTEMPTS = 10;

    // Program details shown when a workshop is selected.
    // Program rows: [topic, speaker|null, time or duration|null]
    // Station rows: [name, description|null, setup]
    // Optional keys: 'program_label', 'stations_label', 'facilitators' (list of names)
    // The workshop catalog supplies the editable name, schedule, venue, capacity,
    // and active state. These legacy entries retain the existing detailed panels.
    public const WORKSHOP_DETAILS = [

        'RA' => [
            'title' => 'Regional Anesthesia Workshop',
            'program' => [['Opening Remarks and Introduction to the Course', 'Dr. Lily Jingco', null], ['Lecture on Updates on Interventional Pain', 'Dr. Richard Genuino', '20 minutes'], ['Demonstration of doing spine ultrasound', 'Dr. Roneil Nogoy', '20 minutes']],
            'session' => '9:00 AM – 12:00 Noon (Workshop Session)',
            'venue' => 'Executive 5',
            'stations' => [['MSK Session', 'Musculoskeletal ultrasound of shoulder, knee, hip and SI joints.', '2 stations with 1 model and 1 UTZ each station'], ['Plan A Blocks', null, '4 stations with 1 model and 1 UTZ each']],
        ],
        'POCUS' => [
            'title' => 'POCUS Workshop',
            'program_label' => 'Lectures',
            'program' => [
                ['Focused Cardiac Ultrasound', 'Dr. Raisalam Macataman', '8:30 – 8:45 AM'],
                ['Lung Ultrasound', 'Dr. Peñafrancia C. Cano', '8:45 – 9:00 AM'],
                ['Gastric Ultrasound', 'Dr. Laiza Teodoro Teoxon', '9:00 – 9:15 AM'],
                ['Airway Ultrasound', 'Dr. Noel Aypa', '9:15 – 9:30 AM'],
                ['Optic Ultrasound', 'Dr. Geraldine Jose', '9:30 – 9:45 AM'],
                ['Coffee Break', null, '9:45 – 10:00 AM'],
            ],
            'session' => '8:30 AM – 12:00 Noon',
            'venue' => 'Executive 5',
            'stations_label' => 'Workshops',
            'stations' => [
                ['Station 1: PLAX/PSAX/A4C', 'Dr. Raisalam Macataman & Dr. Gina Gumintad', '10:00 – 10:20 AM'],
                ['Station 2: Subcostal/IVC/EXUS', 'Dr. Melissa Caranto', '10:20 – 10:40 AM'],
                ['Station 3: Lung', 'Dr. Peñafrancia C. Cano & Dr. Chlenice Dela Cruz', '10:40 – 11:00 AM'],
                ['Station 4: Gastric', 'Dr. Laiza Teodoro Teoxon', '11:00 – 11:20 AM'],
                ['Station 5: Airway', 'Dr. Noel Aypa', '11:20 – 11:40 AM'],
                ['Station 6: Optic', 'Dr. Geraldine Jose', '11:40 AM – 12:00 NN'],
            ],
        ],
        'AIRWAY' => [
            'title' => 'Airway Management Workshop',
            'program' => [],
            'session' => '1:30 – 4:30 PM',
            'venue' => 'Executive 5',
            'stations' => [],
            'facilitators' => [
                'Dr. Roan Jocson',
                'Dr. Maria Margarita Hilvano-Corsiga',
                'Dr. Ralph Philip Anislag',
                'Dr. Ivan Gil Balmadrid',
                'Dr. Jafer Lim',
                'Dr. Lauren Laforteza-Sagadal',
                'Dr. Dennis Lopez',
                'Dr. Hansley Milton Tan',
                'Dr. Christian Soriño',
                'Dr. Emily Nery',
                'Dr. Mark Lee Contillo',
                'Dr. James Monje',
            ],
        ],
    ];

    // MEMBER TYPES:
    protected const MEM_TYPE_MAP = [
        'RM' => 'Regular Member',
        'LM' => 'Life Member',
        'TM' => 'Trainee Member',
    ];

    // Values in the member table that mean "no PRC number on file"
    protected const PRC_PLACEHOLDERS = ['', 'NA', 'NONE', 'NULL', 'NIL', '0'];

    // Identity fields and PRC numbers are displayed and stored in uppercase. Email and hospital keep their normal formats.
    protected const UPPERCASE_DETAIL_FIELDS = ['lastName', 'firstName', 'middleName'];


    protected function normalizeRegistrationDetails(): void
    {
        foreach (self::UPPERCASE_DETAIL_FIELDS as $field) {
            $this->{$field} = mb_strtoupper(trim((string) $this->{$field}));
        }

        $this->prcNumber = mb_strtoupper(preg_replace('/\s+/', '', trim($this->prcNumber)));
    }


    // Public workshops are managed from Filament. Inactive workshops disappear
    // from new registrations but remain available in historical summaries.
    #[Computed]
    public function availableWorkshops(): array
    {
        return Workshop::query()
            ->active()
            ->ordered()
            ->get()
            ->all();
    }

    protected function activeWorkshopCodes(): array
    {
        return array_map(
            static fn (Workshop $workshop): string => $workshop->code,
            $this->availableWorkshops,
        );
    }

    // Registrations per workshop, e.g. ['RA' => 12, 'POCUS' => 0, 'AIRWAY' => 5]
    #[Computed]
    public function taken(): array
    {
        $counts = [];

        foreach ($this->availableWorkshops as $workshop) {
            $counts[$workshop->code] = $this->countFor($workshop->code);
        }

        return $counts;
    }

    #[Computed]
    public function selectedWorkshopSummaries(): array
    {
        return $this->workshopSummaries($this->workshops);
    }

    protected function capacityFor(string $code): int
    {
        foreach ($this->availableWorkshops as $workshop) {
            if ($workshop->code === $code) {
                return max((int) $workshop->capacity, 1);
            }
        }

        return self::WORKSHOP_LIMIT;
    }

    protected function workshopDetailsFor(string $code, ?Workshop $workshop = null): array
    {
        $details = self::WORKSHOP_DETAILS[$code] ?? [];

        if ($workshop !== null) {
            $details['title'] = $workshop->name;
            $details['session'] = $workshop->session ?: ($details['session'] ?? null);
            $details['venue'] = $workshop->venue ?: ($details['venue'] ?? null);
        }

        return $details;
    }

    protected function workshopSummaries(array $codes): array
    {
        $workshops = Workshop::query()
            ->whereIn('code', $codes)
            ->get()
            ->keyBy('code');

        return array_values(array_map(function ($code) use ($workshops): array {
            $code = (string) $code;
            $workshop = $workshops->get($code);
            $details = $this->workshopDetailsFor($code, $workshop);

            return [
                'code' => $code,
                'label' => $workshop?->name
                    ?? ($details['title'] ?? $code),
                'session' => $details['session'] ?? null,
                'venue' => $details['venue'] ?? null,
                'details' => $details,
            ];
        }, $codes));
    }

    // Protected so it can't be called from the browser.
    protected function countFor(string $key): int
    {
        return WorkshopRegistration::whereJsonContains('workshops', $key)->count();
    }

    // Allocates the next four-digit PSA ID for a non-member workshop registrant.
    // The caller holds the workshop-registration lock while this is calculated.
    protected function generateNonMemberPsaId(): string
    {
        $highestNumber = 0;
        $pattern = '/^' . preg_quote(self::NON_MEMBER_PSA_PREFIX, '/') . '(\d{4})$/';

        foreach (
            WorkshopRegistration::where('psa_id', 'like', self::NON_MEMBER_PSA_PREFIX . '%')
                ->lockForUpdate()
                ->pluck('psa_id') as $psaId
        ) {
            if (preg_match($pattern, (string) $psaId, $matches)) {
                $highestNumber = max($highestNumber, (int) $matches[1]);
            }
        }

        $nextNumber = $highestNumber + 1;

        if ($nextNumber > 9999) {
            throw new \OverflowException('The non-member PSA ID sequence is exhausted.');
        }

        return self::NON_MEMBER_PSA_PREFIX . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // True for "", "NA", "N/A", "n.a.", "none", "-" ... (no real PRC number)
    protected function isPlaceholderPrc(?string $value): bool
    {
        $normalized = strtoupper(preg_replace('/[\s\.\/\-_]+/', '', trim((string) $value)));

        return in_array($normalized, self::PRC_PLACEHOLDERS, true);
    }

    // Copies only the member identity data from the database into the component.
    protected function applyMember(Member $member, bool $freshLookup): void
    {
        $code = strtoupper(trim($member->psa_mem_type ?? ''));

        $this->psaId = (string) $member->getKey();
        $this->psaIdInput = '';
        $this->lastName = (string) ($member->mem_last_name ?? '');
        $this->firstName = (string) ($member->mem_first_name ?? '');
        $this->middleName = (string) ($member->mem_middle_name ?? '');
        $this->memType = self::MEM_TYPE_MAP[$code] ?? $code; // "RM" -> "Regular Member"
        $this->prcFromMember = false;

        if ($freshLookup) {
            $this->prcNumber = '';
        }
    }

    // Limits how fast one IP can look up PSA IDs (stops ID enumeration).
    // Returns true when the request may proceed.
    protected function lookupAllowed(): bool
    {
        $key = 'psa-id-lookup:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::LOOKUP_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('psaId', "Too many attempts. Please try again in {$seconds} seconds.");
            return false;
        }

        RateLimiter::hit($key, 60);
        return true;
    }

    // Overwrites identity fields with database values for verified members.
    // Whatever the browser sent for these fields is discarded.
    // Returns false if the member could not be confirmed.
    protected function enforceMemberData(): bool
    {
        if ($this->psaId === '') {
            return true; // non-member, nothing to enforce
        }

        $member = Member::find($this->psaId);

        if (!$member) {
            $this->clearMember();
            $this->addError('psaId', 'PSA ID not found. Please verify again.');
            return false;
        }

        $this->applyMember($member, false);

        return true;
    }

    // The checker only sends the PSA ID. We look the member up again here,
    // so the autofilled values always come from the database.
    #[On('psa-id-selected')]
    public function fillFromMember(string $psaId): void
    {
        if (!$this->lookupAllowed()) {
            return;
        }

        $member = Member::find($psaId); // same lookup as your event registration

        if (!$member) {
            $this->addError('psaId', 'PSA ID not found.');
            return;
        }

        // Already registered: show the error and do NOT autofill anything
        if (WorkshopRegistration::where('psa_id', (string) $member->getKey())->exists()) {
            $this->addError('psaId', 'This PSA ID is already registered for a workshop.');
            return;
        }

        $this->resetErrorBag();

        $this->applyMember($member, true);
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
        $this->reset(['psaId', 'memType', 'lastName', 'firstName', 'middleName', 'prcNumber', 'prcFromMember', 'psaIdInput']);
        $this->resetErrorBag();
    }

    public function submit(): void
    {
        // CHECK 1: Typed a PSA ID but never clicked Verify.
        // Without this, a member could be saved as a "Non-member".
        if ($this->psaId === '' && trim($this->psaIdInput) !== '') {
            $this->addError('psaId', 'Please click Verify for the PSA ID you typed, or clear the field.');
            return;
        }

        // Members: replace whatever the browser sent with the database values.
        if (!$this->enforceMemberData()) {
            return;
        }

        $this->normalizeRegistrationDetails();
        $this->email = trim($this->email);
        $this->phone = trim($this->phone);

        $validated = $this->validate(
            [
                'lastName' => ['required', 'string', 'max:255'],
                'firstName' => ['required', 'string', 'max:255'],
                'middleName' => ['nullable', 'string', 'max:255'],
                'prcNumber' => ['required', 'string', 'min:5', 'max:7'],
                'hospital' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'regex:/^09\d{9}$/'],
                'workshops' => ['required', 'array', 'min:1'],
                'workshops.*' => ['string', 'distinct', Rule::in($this->activeWorkshopCodes())],
            ],
            [
                'lastName.required' => 'Please enter your last name.',
                'firstName.required' => 'Please enter your first name.',
                'prcNumber.required' => 'Please enter your PRC number.',
                'prcNumber.min' => 'PRC number must be at least 5 characters.',
                'prcNumber.max' => 'PRC number must not be more than 7 characters.',
                'hospital.required' => 'Please enter your hospital.',
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'phone.required' => 'Please enter your phone number.',
                'phone.regex' => 'Please enter a valid phone number.',
                'workshops.required' => 'Please select at least one workshop.',
                'workshops.array' => 'Please select valid workshops.',
                'workshops.min' => 'Please select at least one workshop.',
                'workshops.*.in' => 'Please select valid workshops.',
                'workshops.*.distinct' => 'Please select each workshop only once.',
            ],
        );

        $selectedWorkshops = array_values(array_unique($validated['workshops']));

        // CHECK 2: PRC number ownership.
        // If the PRC belongs to a PSA member, only that verified member may use it.
        $memberWithPrc = Member::where('mem_prc_no', $validated['prcNumber'])->first();

        if ($memberWithPrc) {
            if ($this->psaId === '') {
                $this->addError('prcNumber', 'This PRC number belongs to a PSA member. Please verify your PSA ID above.');
                return;
            }

            if ((string) $memberWithPrc->getKey() !== $this->psaId) {
                $this->addError('prcNumber', 'This PRC number belongs to a different PSA member.');
                return;
            }
        }

        if ($this->psaId !== '' && WorkshopRegistration::where('psa_id', $this->psaId)->exists()) {
            $this->addError('psaId', 'This PSA ID is already registered.');
            return;
        }

        if (WorkshopRegistration::where('prc_number', $validated['prcNumber'])->exists()) {
            $this->addError('prcNumber', 'This PRC number is already registered.');
            return;
        }

        // The lock makes capacity, duplicate checks, and insert one operation,
        // so multiple selected workshops cannot be partially reserved.
        try {
            $result = Cache::lock('workshop-registration', 10)->block(5, function () use ($validated, $selectedWorkshops) {
                return DB::transaction(function () use ($validated, $selectedWorkshops) {
                    if ($this->psaId !== '' && WorkshopRegistration::where('psa_id', $this->psaId)->exists()) {
                        return ['status' => 'duplicate_psa'];
                    }

                    if (WorkshopRegistration::where('prc_number', $validated['prcNumber'])->exists()) {
                        return ['status' => 'duplicate_prc'];
                    }

                    foreach ($selectedWorkshops as $workshopCode) {
                        if ($this->countFor($workshopCode) >= $this->capacityFor($workshopCode)) {
                            return [
                                'status' => 'full',
                                'code' => $workshopCode,
                            ];
                        }
                    }

                    $assignedPsaId = $this->psaId !== ''
                        ? $this->psaId
                        : $this->generateNonMemberPsaId();

                    // Returns the saved record so we can email it after the lock is released.
                    return WorkshopRegistration::create([
                        'psa_id' => $assignedPsaId,
                        'mem_last_name' => $validated['lastName'],
                        'mem_first_name' => $validated['firstName'],
                        'mem_middle_name' => $validated['middleName'] ?? '',
                        'mem_type' => $this->psaId !== '' ? $this->memType : 'Non-member',
                        'prc_number' => $validated['prcNumber'],
                        'hospital' => $validated['hospital'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                        'workshops' => $selectedWorkshops,
                    ]);
                });
            });
        } catch (LockTimeoutException $e) {
            $this->addError('workshops', 'The system is busy. Please try again in a moment.');
            return;
        } catch (\OverflowException $e) {
            $this->addError('psaId', 'No non-member PSA IDs are available. Please contact the PSA secretariat.');
            return;
        } catch (UniqueConstraintViolationException $e) {
            // Two submissions raced past the exists() check.
            $this->addError('prcNumber', 'This PRC number is already registered.');
            return;
        }

        if (($result['status'] ?? null) === 'duplicate_psa') {
            $this->addError('psaId', 'This PSA ID is already registered.');
            return;
        }

        if (($result['status'] ?? null) === 'duplicate_prc') {
            $this->addError('prcNumber', 'This PRC number is already registered.');
            return;
        }

        if (($result['status'] ?? null) === 'full') {
            $fullCode = (string) $result['code'];
            $this->workshops = array_values(array_diff($this->workshops, [$fullCode]));
            $fullWorkshop = $this->workshopSummaries([$fullCode])[0] ?? ['label' => $fullCode];
            $this->addError(
                'workshops',
                "Sorry, {$fullWorkshop['label']} is already full. Please choose another workshop.",
            );
            return;
        }

        $this->psaId = (string) $result->psa_id;
        $this->workshops = $selectedWorkshops;
        $this->confirmationEmailSent = false;

        // Confirmation email. A mail failure must not undo a saved registration.
        try {
            Mail::to($result->email)->send(new WorkshopRegistrationConfirmation(
                $result,
                $this->workshopSummaries($selectedWorkshops),
            ));
            $this->confirmationEmailSent = true;
        } catch (\Throwable $e) {
            Log::error('Workshop confirmation email failed.', [
                'registration_id' => $result->getKey(),
                'exception' => $e,
            ]);
        }

        $this->submitted = true;
    }
};
?>

<div>
    @if ($submitted)
        @php
            $confirmationWorkshops = $this->selectedWorkshopSummaries;
        @endphp
        <div class="mx-auto max-w-2xl overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100">
            <div class="h-1.5 bg-gradient-to-r from-[#000066] to-[#0000aa]"></div>
            <div class="p-6 sm:p-8">
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                        aria-hidden="true">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 7" />
                        </svg>
                    </div>
                    <p class="mt-5 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">Registration
                        confirmed</p>
                    <h2 class="mt-2 text-2xl font-bold text-[#000066]">You're successfully registered</h2>
                    <p class="mt-2 max-w-lg text-sm leading-6 text-gray-500">
                        Thank you, {{ $firstName }} {{ $lastName }}. Your selected workshop slots have been recorded.
                </div>

                <div class="mt-7 overflow-hidden rounded-xl border border-[#000066]/10">
                    <div class="bg-[#000066] px-4 py-3 text-xs font-semibold uppercase tracking-wider text-white">
                        Registration summary
                    </div>
                    <dl class="divide-y divide-gray-100">
                        @foreach ($confirmationWorkshops as $confirmationWorkshop)
                            <div class="px-4 py-3">
                                <dt class="text-xs text-gray-400">Workshop</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-700">
                                    {{ $confirmationWorkshop['label'] }}
                                </dd>
                                @if ($confirmationWorkshop['session'] || $confirmationWorkshop['venue'])
                                    <div class="mt-2 space-y-1 text-xs text-gray-500">
                                        @if ($confirmationWorkshop['session'])
                                            <p><span class="font-semibold text-gray-600">Schedule:</span>
                                                {{ $confirmationWorkshop['session'] }}</p>
                                        @endif
                                        @if ($confirmationWorkshop['venue'])
                                            <p><span class="font-semibold text-gray-600">Venue:</span>
                                                {{ $confirmationWorkshop['venue'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="mt-5 rounded-xl border p-4 {{ $confirmationEmailSent ? 'border-emerald-100 bg-emerald-50' : 'border-amber-100 bg-amber-50' }}"
                    role="status" aria-live="polite">
                    <div class="flex items-start gap-3">
                        @if ($confirmationEmailSent)
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 8.5 12 14l9-5.5M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Confirmation email sent</p>
                                <p class="mt-1 text-xs leading-5 text-emerald-700">
                                    We sent the confirmation to <span class="font-semibold">{{ $email }}</span>.
                                    Please check your inbox and spam folder.
                                </p>
                            </div>
                        @else
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v4m0 4h.01M10.3 3.8 2.7 17a2 2 0 0 0 1.74 3h15.12a2 2 0 0 0 1.74-3L13.7 3.8a2 2 0 0 0-3.4 0Z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-amber-800">Registration saved</p>
                                <p class="mt-1 text-xs leading-5 text-amber-700">
                                    Your slot was recorded, but we could not send the confirmation email. For further
                                    inquiries, contact us at <span class="font-semibold">psainc_sec@yahoo.com</span>.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>


                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <a href="{{ route('workshop') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-[#000066] px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90">
                        Return to workshop page
                    </a>
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                        Back to home
                    </a>
                </div>
            </div>
        </div>
    @else
        {{-- PSA ID checker (optional, members only): choosing a result fires 'psa-id-selected' --}}

        <x-event-registration.event-registration-layout>
            <livewire:psa-id-checker mode="both" heading="Find Your PSA ID" />

            @php
                // True once a PSA ID is verified: name fields are locked
                $memberLocked = $psaId !== '';
                $lockedClass = 'bg-gray-50 text-gray-700 cursor-not-allowed';
            @endphp

            <form wire:submit="submit" class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-[#000066] to-[#0000aa]"></div>
                <div class="p-6 space-y-6">

                    <div>
                        <h2 class="font-bold text-base text-[#000066]">Workshop Registration</h2>
                        <p class="text-gray-700 text-xs mt-0.5">
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

                        @if ($memberLocked)
                            <p class="text-xs text-gray-400 sm:col-span-2">
                                Name details are taken from the PSA records and cannot be edited here.
                                To correct them, please contact the PSA secretariat.
                            </p>
                        @endif

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="lastName" placeholder="Last name" @readonly($memberLocked)
                                tabindex="{{ $memberLocked ? '-1' : '0' }}" maxlength="255" autocapitalize="characters"
                                oninput="this.value = this.value.toUpperCase()"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-[#000066] {{ $memberLocked ? $lockedClass : '' }}">

                            @error('lastName')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="firstName" placeholder="First name" @readonly($memberLocked)
                                tabindex="{{ $memberLocked ? '-1' : '0' }}" maxlength="255" autocapitalize="characters"
                                oninput="this.value = this.value.toUpperCase()"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-[#000066] {{ $memberLocked ? $lockedClass : '' }}">
                            @error('firstName')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Middle
                                Name</label>
                            <input type="text" wire:model="middleName" placeholder="Middle name (optional)"
                                @readonly($memberLocked) tabindex="{{ $memberLocked ? '-1' : '0' }}" maxlength="255"
                                autocapitalize="characters" oninput="this.value = this.value.toUpperCase()"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-[#000066] {{ $memberLocked ? $lockedClass : '' }}">
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
                                minlength="5" maxlength="7" autocapitalize="characters"
                                oninput="this.value = this.value.toUpperCase()"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-[#000066]">
                            @if ($psaId)
                                <p class="text-gray-400 text-xs mt-1">Please enter your PRC number manually.</p>
                            @endif
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
                            <input type="tel" wire:model="phone" placeholder="e.g. 09171234567" autocomplete="tel"
                                inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                                title="Please enter a valid PH mobile number (e.g. 09123456789)."
                                oninput="this.value = this.value.replace(/\D/g, '')"
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

                    {{-- workshop selection: multiple choices --}}
                    <div>
                        <fieldset>
                            <legend class="block text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">
                                Select Workshops <span class="text-red-500">*</span>
                                <span class="text-gray-400 normal-case font-normal">(choose all that apply)</span>
                            </legend>
                            <p class="mb-3 text-xs text-gray-500">
                                Each selected workshop reserves one slot for this registrant.
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @forelse ($this->availableWorkshops as $workshop)
                                    @php
                                        $key = $workshop->code;
                                        $slotsTaken = $this->taken[$key] ?? 0;
                                        $capacity = max((int) $workshop->capacity, 1);
                                        $slotsLeft = max($capacity - $slotsTaken, 0);
                                        $isFull = $slotsLeft === 0;
                                        $isSelected = in_array($key, $workshops, true);
                                    @endphp
                                    <label wire:key="ws-{{ $key }}"
                                        class="flex items-center gap-3 border rounded-lg px-4 py-3 transition
                                        {{ $isFull && !$isSelected ? 'opacity-60 cursor-not-allowed bg-gray-50 border-gray-200' : 'cursor-pointer' }}
                                        {{ $isSelected ? 'border-[#000066] bg-[#f4f4fb]' : 'border-gray-200 hover:bg-gray-50' }}">
                                        <input type="checkbox" wire:model.live="workshops" value="{{ $key }}"
                                            name="workshops[]" @disabled($isFull && !$isSelected)
                                            class="w-4 h-4 border-gray-300 text-[#000066] focus:ring-[#000066]">
                                        <div class="min-w-0">
                                            <span class="block text-sm font-medium text-gray-700">{{ $workshop->name }}</span>
                                            @if ($isFull)
                                                <span class="block text-xs font-semibold text-red-500">Full</span>
                                            @else
                                                <span
                                                    class="block text-xs {{ $slotsLeft <= 10 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
                                                    {{ $slotsTaken }} / {{ $capacity }} registered
                                                    · {{ $slotsLeft }} {{ Str::plural('slot', $slotsLeft) }} left
                                                </span>
                                            @endif
                                        </div>
                                    </label>
                                @empty
                                    <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 sm:col-span-3">
                                        No workshops are currently open for registration.
                                    </p>
                                @endforelse
                            </div>
                        </fieldset>

                        {{-- Selected workshop details --}}
                        @foreach ($this->selectedWorkshopSummaries as $selectedWorkshop)
                            @php
                                $details = $selectedWorkshop['details'];
                                $workshopCode = $selectedWorkshop['code'];
                            @endphp

                            @if ($details && (!empty($details['program']) || !empty($details['stations']) || !empty($details['facilitators']) || !empty($details['session']) || !empty($details['venue'])))
                                <div wire:key="ws-details-{{ $workshopCode }}" x-data x-transition
                                    class="mt-4 rounded-xl border border-[#000066]/15 bg-[#f4f4fb] p-5 space-y-4">

                                <h3 class="font-bold text-sm text-[#000066] uppercase tracking-wide">
                                    {{ $details['title'] }}
                                </h3>
                                @if (!empty($details['program']))
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                            {{ $details['program_label'] ?? 'Program' }}</p>
                                        <div class="overflow-x-auto rounded-lg border border-[#000066]/15 bg-white">
                                            <table class="w-full text-sm text-left">
                                                <thead class="bg-[#000066] text-white text-xs uppercase tracking-wide">
                                                    <tr>
                                                        <th class="px-4 py-2.5 font-semibold">Topic</th>
                                                        <th class="px-4 py-2.5 font-semibold">Speaker</th>
                                                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap">
                                                            Time / Duration
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
                                                                {{ $speaker ?? '—' }}</td>
                                                            <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">
                                                                {{ $duration ?? '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                                @if (!empty($details['session']) || !empty($details['venue']))
                                    <div class="rounded-lg bg-[#000066] text-white px-4 py-3 space-y-2">
                                        @if (!empty($details['session']))
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

                                        @if (!empty($details['venue']))
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
                                @if (!empty($details['stations']))
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                            {{ $details['stations_label'] ?? 'Workshop Stations' }}</p>
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
                                @if (!empty($details['facilitators']))
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                            Facilitators</p>
                                        <ul
                                            class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-sm text-gray-700">
                                            @foreach ($details['facilitators'] as $facilitator)
                                                <li class="flex items-start gap-2">
                                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-[#000066] shrink-0"></span>
                                                    <span>{{ $facilitator }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endif

                            @endforeach
                        @error('workshops')
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