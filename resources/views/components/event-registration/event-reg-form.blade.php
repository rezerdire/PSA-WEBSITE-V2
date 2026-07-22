<?php

    use App\Models\Member;
    use App\Models\Registration;
    use Livewire\Component;
    use Livewire\WithFileUploads;
    use Illuminate\Validation\Rule;
    use App\Mail\RegistrationConfirmed;
    use Illuminate\Support\Facades\Mail;

    new class extends Component {

        use WithFileUploads;

        // PSA Verification
        public string $psaId          = '';
        public bool   $memberVerified = false;
        public string $verifyError    = '';

        // Auto-filled from members table
        public string $firstName  = '';
        public string $lastName   = '';
        public string $middleName = '';
        public string $membership = '';

        // Contact Details
        public string $prcNumber       = '';
        public string $email           = '';
        public string $contactNumber   = '';
        public string $hospitalName    = '';
        public string $hospitalAddress = '';
        public string $country         = '';

        // Discount & Payment
        public string $discountType = 'non_disc';
        public        $discountImg  = null;
        public        $paymentProof = null;

        public bool   $submitted      = false;
        public string $registrationId = '';

        protected const MEM_TYPE_MAP = [
            'RM' => 'Regular Member',
            'LM' => 'Life Member',
            'TM' => 'Trainee Member',
        ];

        public function verify(): void
    {
        $this->validate(
            ['psaId' => ['required', 'digits:4']],
            ['psaId.digits' => 'PSA ID must be exactly 4 digits.']
        );

        $this->verifyError    = '';
        $this->memberVerified = false;

        $member = Member::find($this->psaId);

        if (!$member) {
            $this->verifyError = 'PSA ID not found. Please double-check your ID number.';
            return;
        }

        $memType = strtoupper(trim($member->psa_mem_type ?? ''));

        if (!array_key_exists($memType, self::MEM_TYPE_MAP)) {
            $this->verifyError = 'Your membership type is not eligible for online registration.';
            return;
        }

        // Only block if there's a Pending or Approved registration.
        // Rejected registrations are allowed to resubmit.
        if (Registration::where('psa_id', $this->psaId)
            ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
            ->exists()
        ) {
            $this->verifyError = 'This PSA ID has already been registered for this event.';
            return;
        }

        $this->firstName      = $member->mem_first_name  ?? '';
        $this->lastName       = $member->mem_last_name   ?? '';
        $this->middleName     = $member->mem_middle_name ?? '';
        $this->membership     = $memType;
        $this->memberVerified = true;
    }
        public function resetVerification(): void
        {
            $this->psaId          = '';
            $this->memberVerified = false;
            $this->verifyError    = '';
            $this->firstName      = '';
            $this->lastName       = '';
            $this->middleName     = '';
            $this->membership     = '';
        }

        protected function rules(): array
        {
            return [
                'psaId'           => ['required', 'string', 'size:4'],
                'firstName'       => ['required', 'string', 'max:255'],
                'lastName'        => ['required', 'string', 'max:255'],
                'middleName'      => ['nullable', 'string', 'max:255'],
                'membership'      => ['required', Rule::in(['RM', 'LM', 'TM'])],

                'prcNumber'       => ['required', 'digits:7'],
                'email'           => ['required', 'email', 'max:255'],
                'contactNumber'   => ['required', 'regex:/^09\d{9}$/'],
                'hospitalName'    => ['required', 'string', 'max:255'],
                'hospitalAddress' => ['required', 'string', 'max:255'],
                'country'         => ['required', 'string', 'max:255'],

                'discountType'    => ['required', Rule::in(['senior_disc', 'non_disc'])],
                'discountImg'     => [
                    'nullable',
                    Rule::requiredIf($this->discountType === 'senior_disc'),
                    'image', 'max:5120',
                ],
                'paymentProof'    => ['required', 'image', 'max:5120'],
            ];
        }

        public function submit(): void
    {
        if (!$this->memberVerified) {
            $this->addError('psaId', 'Please verify your PSA ID before submitting.');
            return;
        }

        $this->validate();

        $member = Member::find($this->psaId);
        if (!$member) {
            $this->verifyError    = 'PSA ID could not be re-verified. Please refresh and try again.';
            $this->memberVerified = false;
            return;
        }

        if (
            strtolower(trim($member->mem_last_name))  !== strtolower(trim($this->lastName)) ||
            strtolower(trim($member->mem_first_name)) !== strtolower(trim($this->firstName))
        ) {
            $this->verifyError    = 'Member data mismatch. Please re-verify your PSA ID.';
            $this->memberVerified = false;
            return;
        }

        // Block only on Pending/Approved — allow resubmission if previously Rejected.
        $existing = Registration::where('psa_id', $this->psaId)
            ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
            ->exists();

        if ($existing) {
            $this->verifyError    = 'This PSA ID has already been registered.';
            $this->memberVerified = false;
            return;
        }


        // where the uploaded files will be stored
            $discountPath = null;
            if ($this->discountType === 'senior_disc' && $this->discountImg) {
                $discountPath = $this->discountImg->store('Registration/ID-Upload', 'uploads');
            }

            $paymentPath = $this->paymentProof->store('Registration/ProofofPayment', 'uploads');

        // If a Rejected registration already exists for this PSA ID, update that
        // same row back to Pending instead of creating a duplicate record.
        $registration = Registration::updateOrCreate(
            ['psa_id' => $this->psaId],
            [
                'prc_number'       => (int) $this->prcNumber,
                'last_name'        => $this->lastName,
                'first_name'       => $this->firstName,
                'middle_name'      => $this->middleName,
                'hospital_name'    => $this->hospitalName,
                'hospital_address' => $this->hospitalAddress,
                'email'            => $this->email,
                'contact_number'   => $this->contactNumber,
                'membership'       => $this->membership,
                'discount_id'      => $discountPath,
                'proof_payment'    => $paymentPath,
                'status'           => Registration::STATUS_PENDING,
                'country'          => $this->country,
                'rejection_title'  => null,
                'rejection_reason' => null,
            ]
        );

        // SENDING CONFIRMATION EMAIL
        Mail::to($this->email)->send(new RegistrationConfirmed($registration));

        $this->registrationId = (string) $registration->id;
        $this->submitted      = true;

        // adding this event to the browser's window so that the frontend can scroll to top
            $this->dispatch('registration-submitted');

    }
    };
    ?>
    {{-- FRONTEND --}}
    <div class="p-6 md:p-10" x-data
        x-on:registration-submitted.window="$nextTick(() => { document.getElementById('registration-success')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); })"
        x-on:validation-failed.window="$nextTick(() => { document.getElementById('error-summary')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); })">
    {{-- script that will scroll to the registration success section form every time a user submit a registration --}}
    @if ($submitted)
        <div class="max-w-lg mx-auto py-10" id="registration-success">

                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center" style="background-color: #e8f5e9;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" style="color: #2e7d32;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold mb-2" style="color: #000066;">Registration Submitted!</h2>
                    <p class="text-gray-500 text-sm">
                        Your registration for <span class="font-semibold text-gray-700">PSA Annual Convention 2026</span> has been received and is currently pending review.
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-100 overflow-hidden mb-6">
                    <div class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-white" style="background-color: #000066;">
                        Registration Summary
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach ([
                            // ['Reference No.',  '#' . str_pad($registrationId, 6, '0', STR_PAD_LEFT)],
                            ['Full Name',       $firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName],
                            ['PSA ID',          $psaId],
                            ['Membership',      ['RM' => 'Regular Member', 'LM' => 'Life Member', 'TM' => 'Trainee Member'][$membership] ?? $membership],
                            ['Email',           $email],
                            ['Contact No.',     $contactNumber],
                            ['Hospital',        $hospitalName],
                            ['Status',          'Pending Review'],
                        ] as [$label, $value])
                            <div class="flex items-start gap-4 px-5 py-3">
                                <span class="text-xs text-gray-400 w-28 shrink-0 pt-0.5">{{ $label }}</span>
                                <span class="text-sm font-medium text-gray-700 {{ $label === 'Status' ? 'text-amber-600' : '' }}">
                                    {{ $value }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 flex gap-3 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z" />
                    </svg>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        {{-- Please take note of your <strong>Reference No.</strong> above.  --}}
                        The PSA secretariat will review your submission and update your registration status. You may follow up using your PSA ID <strong>{{ $psaId }}</strong>.
                    </p>
                </div>

                <div class="flex justify-center">
                    <a href="{{ url('/') }}"
                        class="px-8 py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                        style="background-color: #000066;">
                        Back to Home
                    </a>
                </div>

            </div>

        @else

            <h2 class="text-xl font-bold mb-1" style="color: #000066;">Registration Form</h2>
            <p class="text-gray-400 text-sm mb-8">All fields are required unless stated otherwise.</p>

            {{-- PSA ID Verification --}}
            <div class="mb-8">
                <x-event-registration.section-title title="Step 1 - Verify PSA ID" />

                @if (!$memberVerified)
                    <div class="flex gap-3 items-start">
                        <div class="flex-1">
                            <input
                                type="text"
                                wire:model="psaId"
                                wire:keydown.enter="verify"
                                placeholder="Enter your 4-digit PSA ID"
                                maxlength="4"
                                inputmode="numeric"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#000066]">
                            @error('psaId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            @if ($verifyError)
                                <p class="text-xs text-red-500 mt-1">{{ $verifyError }}</p>
                            @endif
                        </div>
                        <button type="button" wire:click="verify" wire:loading.attr="disabled" wire:target="verify"
                            class="shrink-0 px-5 py-3 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50 bg-[#000066]">
                            <span wire:loading.remove wire:target="verify">Verify</span>
                            <span wire:loading wire:target="verify">Checking…</span>
                        </button>
                    </div>
                @else
                    <div class="flex items-center justify-between gap-4 bg-green-50 border border-blue-100 rounded-xl px-5 py-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 bg-green-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">
                                    {{ $firstName }} {{ $middleName }} {{ $lastName }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    PSA ID: <span class="font-mono font-semibold">{{ $psaId }}</span>
                                    &nbsp;·&nbsp;
                                    {{ ['RM' => 'Regular Member', 'LM' => 'Life Member', 'TM' => 'Trainee Member'][$membership] ?? $membership }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="resetVerification"
                            class="shrink-0 text-xs font-semibold text-blue-700 hover:text-blue-900 transition">
                            Change
                        </button>
                    </div>
                @endif
            </div>

            @if (!$memberVerified)
                <div class="rounded-xl border border-yellow-100 bg-yellow-50 px-5 py-4 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <p class="text-xs text-yellow-800">Verify your PSA ID above to unlock the registration form.</p>
                </div>
            @else

                <form wire:submit="submit">

                    {{-- Member Information --}}
                    <div class="mb-8">
                        <x-event-registration.section-title title="Member Information" />
                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-4">
                            <div class="sm:col-span-1">
                                <x-form.readonly-field label="PSA ID No." :value="$psaId"  mono />
                            </div>
                            <div class="sm:col-span-2">
                                <x-form.readonly-field label="First Name" :value="$firstName" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-form.readonly-field label="Last Name" :value="$lastName" />
                            </div>
                            <div class="sm:col-span-1">
                                <x-form.readonly-field label="Middle Name" :value="$middleName" />
                            </div>
                        </div>
                    </div>

                    {{-- Contact Details --}}
                    <div class="mb-8">
                        <x-event-registration.section-title title="Contact Details" />
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <x-form.input label="PRC Number" hint="(7 digits)" name="prcNumber" wire:model="prcNumber" pattern="^\d{7}$"
                                placeholder="1234567" maxlength="7" inputmode="numeric"   pattern-message="PRC number must be exactly 7 digits."/>

                            <x-form.input label="Email Address" type="email" name="email" wire:model="email"
                                placeholder="you@example.com" />
                                
                            <x-form.input label="Contact Number" name="contactNumber" pattern="^09\d{9}$" pattern-message="Please enter a valid PH mobile number (e.g. 09123456789)."
                                placeholder="09XXXXXXXXX" inputmode="numeric" maxlength="11" />

                            <div class="sm:col-span-2">
                                <x-form.input label="Hospital / Institution Name" name="hospitalName" wire:model="hospitalName"
                                    placeholder="Name of Hospital" />
                            </div>

                            <x-form.input label="Hospital Address" name="hospitalAddress" wire:model="hospitalAddress"
                                placeholder="City, Province" />

                            <x-form.input label="Country" name="country" wire:model="country" placeholder="Philippines" />
                        </div>
                    </div>

                    {{-- Membership & Discount --}}
                    <div class="mb-8">
                        <x-event-registration.section-title title="Membership & Discount" />
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-3">Membership Type</label>
                                <div class="space-y-2">
                                    @foreach ([['RM', 'Regular Member'], ['LM', 'Life Member'], ['TM', 'Trainee Member']] as [$code, $label])
                                        <x-form.radio-option
                                            :value="$code"
                                            :label="$label"
                                            :active="$membership === $code"
                                            :disabled="true"
                                            color="blue" />
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-400 mt-2">* Auto-filled from your PSA membership record</p>
                            </div>

                            <div x-data="{ disc: @entangle('discountType') }">
                                <label class="block text-xs font-medium text-gray-500 mb-3">Discount</label>
                                <div class="space-y-2 mb-4">
                                    @foreach ([['senior_disc', 'Senior Citizen/PWD'], ['non_disc', 'None']] as [$value, $label])
                                        <x-form.radio-option
                                            :value="$value"
                                            :label="$label"
                                            model="disc"
                                            color="red" />
                                    @endforeach
                                </div>
                                <div x-show="disc === 'senior_disc'" x-transition>
                                    <x-event-registration.image-upload
                                        name="discount_img"
                                        wireModel="discountImg"
                                        label="Senior Citizen ID"
                                        color="#000066" />
                                    @error('discountImg') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Proof of Payment --}}
                    <div class="mb-6">
                        <x-event-registration.section-title title="Proof of Payment" />
                        <x-event-registration.image-upload
                            name="payment_proof"
                            wireModel="paymentProof"
                            label="Payment Screenshot"
                            :required="true"
                            color="#ac071a" />
                        @error('paymentProof') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Error Summary (all validation errors, shown right before Submit) --}}
                    @if ($errors->any())
                        <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 mb-6" id="error-summary">
                            <p class="text-sm font-bold text-red-700 mb-2">
                                Please check the following fields before submitting:
                            </p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-xs text-red-600">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Submit --}}
                    <div class="flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                            class="px-8 py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50 
                            bg-[#000066]">
                            <span wire:loading.remove wire:target="submit">Submit Registration</span>
                            <span wire:loading wire:target="submit">Submitting…</span>
                        </button>
                    </div>

                </form>
            @endif
        @endif
    </div>