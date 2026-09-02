<?php

    use App\Models\Registration;
    use Livewire\Component;
    use Livewire\WithFileUploads;
    use App\Mail\RegistrationConfirmed;
    use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Facades\DB;

    new class extends Component {

        use WithFileUploads;

        
        public string $firstName  = '';
        public string $lastName   = '';
        public string $middleName = '';
        public string $membership = 'NM'; //NON MEMBER

        // Contact Details
        public string $prcNumber       = '';
        public string $email           = '';
        public string $contactNumber   = '';
        public string $hospitalName    = '';
        public string $hospitalAddress = '';
        public string $country         = '';

        // Payment (non-members are always fixed price / non-discounted, like Life Members)
        public string $discountType = 'non_disc';
        public        $paymentProof = null;

        // upload-in-progress flag — set/cleared by Alpine from Livewire's
        // native upload events, and used both to show a loader and to block submit.
        public bool $paymentUploading = false;

        // NEW: confirmation-before-submit step
        public bool $showConfirm = false;

        public bool   $submitted      = false;
        public string $registrationId = '';

        // Live PRC duplicate check status: '', 'checking', 'duplicate', 'available'
        public string $prcCheckStatus = '';

        protected function rules(): array
        {
            return [
                'firstName'       => ['required', 'string', 'max:255'],
                'lastName'        => ['required', 'string', 'max:255'],
                'middleName'      => ['nullable', 'string', 'max:255'],

                'prcNumber' => ['required', 'digits_between:5,7'],
                'email'           => ['required', 'email', 'max:255'],
                'contactNumber'   => ['required', 'regex:/^09\d{9}$/'],
                'hospitalName'    => ['required', 'string', 'max:255'],
                'hospitalAddress' => ['required', 'string', 'max:255'],
                'country'         => ['required', 'string', 'max:255'],

                'paymentProof'    => ['required', 'image', 'max:5120'],
            ];
        }


        protected function generateGuestPsaId(): string
        {
            return DB::transaction(function () {
                $last = Registration::where('psa_id', 'like', 'NM\_%') //where psa_id value has NM
                //in case a two or more user try to create or update a specific data at the same time
                //with that saying it will prevent to overwrite the action of the user/admin
                    ->lockForUpdate() 
                    ->orderByRaw('CAST(SUBSTRING(psa_id, 4) AS UNSIGNED) DESC') //organizing a proper order since psa_id has 4 digit order
                    ->value('psa_id'); 

                $nextNumber = 1;

                if ($last && preg_match('/^NM_(\d+)$/', $last, $matches)) {
                    $nextNumber = ((int) $matches[1]) + 1;
                } // increment +1 ex: nm_0001 - next would be nm_0002

                return 'NM_' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT); // string + the increment
            });
        }

        /**
         * PRC numbers must be unique across ALL registrations regardless of
         * membership type — a member's psa_id won't start with "NM_" but the
         * PRC number still needs to be checked against them too, since the
         * "NM_xxxx" prefix is only a guest ID label, not a PRC scope.
         */
        protected function prcNumberAlreadyRegistered(): bool
        {
            return Registration::where('prc_number', $this->prcNumber)
                ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
                ->exists();
        }

        /**
         * NEW: Live-check hook. Livewire calls this automatically whenever
         * $prcNumber changes (via wire:model.live on the field). Gives the
         * user instant feedback before they ever hit submit. This is UX only
         * — the authoritative check still runs server-side in
         * reviewSubmission()/submit() to guard against races and JS bypass.
         */
        public function updatedPrcNumber(): void
        {
            $this->resetErrorBag('prcNumber');
            $this->prcCheckStatus = '';

            // Don't hit the DB until it's a plausible PRC number.
            if (!preg_match('/^\d{5,7}$/', $this->prcNumber)) {
                return;
            }

            if ($this->prcNumberAlreadyRegistered()) {
                $this->prcCheckStatus = 'duplicate';
                $this->addError('prcNumber', 'This PRC number is already registered.');
            } else {
                $this->prcCheckStatus = 'available';
            }
        }

        /**
         * NEW: Step before the real submit. Runs full validation and, if
         * everything passes, opens the "please double-check" confirmation
         * modal instead of saving anything yet.
         */
        public function reviewSubmission(): void
        {
            // Non-members are always fixed price / non-discounted, regardless of anything client-side.
            $this->discountType = 'non_disc';

            if ($this->paymentUploading) {
                $this->addError('paymentProof', 'Please wait for the upload to finish before submitting.');
                return;
            }

            $this->validate();

            if ($this->prcNumberAlreadyRegistered()) {
                $this->addError('prcNumber', 'This PRC number is already registered.');
                return;
            }

            $this->showConfirm = true;
            $this->dispatch('open-confirm-modal');
        }

        public function cancelReview(): void
        {
            $this->showConfirm = false;
        }

        public function submit(): void
        {
            // Non-members are always fixed price / non-discounted, regardless of anything client-side.
            $this->discountType = 'non_disc';

            if ($this->paymentUploading) {
                $this->addError('paymentProof', 'Please wait for the upload to finish before submitting.');
                $this->showConfirm = false;
                return;
            }

            $this->validate();

            if ($this->prcNumberAlreadyRegistered()) {
                $this->addError('prcNumber', 'This PRC number is already registered.');
                $this->showConfirm = false;
                return;
            }

            $paymentPath = $this->paymentProof
                ? $this->paymentProof->store('Registration/ProofofPayment', 'uploads')
                : null;

            $registration = Registration::create([
                'psa_id'           => $this->generateGuestPsaId(),
                'prc_number'       => (int) $this->prcNumber,
                'last_name'        => $this->lastName,
                'first_name'       => $this->firstName,
                'middle_name'      => $this->middleName,
                'hospital_name'    => $this->hospitalName,
                'hospital_address' => $this->hospitalAddress,
                'email'            => $this->email,
                'contact_number'   => $this->contactNumber,
                'membership'       => 'NM',
                'discount_id'      => null,
                'proof_payment'    => $paymentPath,
                'status'           => Registration::STATUS_PENDING,
                'country'          => $this->country,
                'rejection_title'  => null,
                'rejection_reason' => null,
            ]);

            // SENDING CONFIRMATION EMAIL
            Mail::to($this->email)->send(new RegistrationConfirmed($registration));

            $this->registrationId = (string) $registration->id;
            $this->submitted      = true;
            $this->showConfirm    = false;

            // adding this event to the browser's window so that the frontend can scroll to top
            $this->dispatch('registration-submitted');
        }
    };
    ?>
    {{-- FRONTEND --}}
    <div class="p-6 md:p-10" x-data
        x-on:registration-submitted.window="$nextTick(() => { document.getElementById('registration-success')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); })"
        x-on:validation-failed.window="$nextTick(() => { document.getElementById('error-summary')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); })">
    {{-- submission --}}
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
                            ['Full Name',   $firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName],
                            ['Membership',  'Non-Member'],
                            ['Email',       $email],
                            ['Contact No.', $contactNumber],
                            ['Hospital',    $hospitalName],
                            ['Status',      'Pending Review'],
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
                        The PSA secretariat will review your submission and update your registration status. You may follow up using your email <strong>{{ $email }}</strong>.
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

            <h2 class="text-xl font-bold mb-1" style="color: #000066;">Registration Form (Non-Member)</h2>
            <p class="text-gray-400 text-sm mb-8">All fields are required unless stated otherwise.</p>

            {{-- NEW: submit is now handled by reviewSubmission(), which validates
                     then opens the confirmation modal. The actual save only happens
                     when the user confirms inside that modal. --}}
            <form wire:submit.prevent="reviewSubmission">

                {{-- Member Information --}}
                <div class="mb-8">
                    <x-event-registration.section-title title="Your Information" />
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-form.input label="First Name" name="firstName" wire:model="firstName"
                            placeholder="Christian" />
                        <x-form.input label="Last Name" name="lastName" wire:model="lastName"
                            placeholder="Vacaro" />
                        <x-form.input label="Middle Name" hint="(optional)" name="middleName" wire:model="middleName"
                            placeholder="Middle Name" />
                    </div>
                </div>

                {{-- Contact Details --}}
                <div class="mb-8">
                    <x-event-registration.section-title title="Contact Details" />
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-form.input label="PRC Number" hint="(5-7 digits)" name="prcNumber" wire:model.live.debounce.500ms="prcNumber"  pattern="^\d{5,7}$"
                                placeholder="1234567" minlength="5" maxlength="7" inputmode="numeric" pattern-message="PRC number must be between 5 and 7 digits."/>

                            <div wire:loading wire:target="prcNumber" class="flex items-center gap-1.5 text-xs text-gray-400 mt-1">
                                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Checking PRC number…
                            </div>

                            @if ($prcCheckStatus === 'available' && !$errors->has('prcNumber'))
                                <p wire:loading.remove wire:target="prcNumber" class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    PRC number available
                                </p>
                            @endif
                        </div>

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

                        <div class="sm:col-span-3">
                            <x-form.input label="Country" name="country" wire:model="country" placeholder="Philippines" />
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

                    <div wire:loading wire:target="paymentProof"
                        class="flex items-center gap-2 text-xs text-gray-500 mt-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Uploading, please wait…
                    </div>

                    @error('paymentProof') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- error bullet field --}}
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
                    <button type="submit" wire:loading.attr="disabled" wire:target="reviewSubmission,paymentProof,prcNumber"
                        @if ($prcCheckStatus === 'duplicate') disabled @endif
                        class="px-8 py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50
                        bg-[#000066]">
                        <span wire:loading.remove wire:target="reviewSubmission,paymentProof,prcNumber">Submit Registration</span>
                        <span wire:loading wire:target="reviewSubmission">Checking…</span>
                        <span wire:loading wire:target="paymentProof">Waiting for upload…</span>
                        <span wire:loading wire:target="prcNumber">Verifying PRC…</span>
                    </button>
                </div>

            </form>

            {{-- confirmation modal --}}
            @if ($showConfirm)

                <div class="fixed inset-0 z-[9999] bg-black/70 sm:flex sm:items-center sm:justify-center sm:p-4"
                    wire:key="confirm-registration-backdrop">

                    <div
                        class="flex h-[100dvh] w-full flex-col bg-white
                   sm:h-auto sm:max-h-[90dvh] sm:max-w-2xl
                   sm:rounded-3xl sm:shadow-2xl">

                        {{-- Header --}}
                        <div class="shrink-0 px-4 py-4 sm:px-7 sm:py-6" style="background-color:#000066;">

                            <div class="flex items-start gap-3">

                                {{-- Warning icon --}}
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400 sm:h-12 sm:w-12">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white sm:h-6 sm:w-6"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">

                                    <p
                                        class="text-[9px] font-bold uppercase tracking-[0.2em] text-blue-200 sm:text-[10px]">
                                        Final Review
                                    </p>

                                    <h3 class="mt-0.5 text-base font-bold leading-tight text-white sm:text-xl">
                                        Please Double-Check Your Information
                                    </h3>

                                    <p class="mt-1 text-[11px] leading-relaxed text-blue-100 sm:text-sm">
                                        Review your information carefully before submitting.
                                    </p>

                                </div>

                            </div>

                        </div>

                        {{-- body --}}
                        <div class="min-h-0 flex-1 overflow-y-auto">

                            {{-- Important warning --}}
                            <div class="px-4 pt-4 sm:px-7 sm:pt-5">

                                <div
                                    class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 sm:rounded-2xl sm:p-5">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="mt-0.5 h-6 w-6 shrink-0 text-red-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    </svg>

                                    <div class="min-w-0">

                                        <p class="text-base font-extrabold leading-tight text-red-900 sm:text-lg">
                                            Important: This action cannot be undone.
                                        </p>

                                        <p class="mt-2 text-sm leading-relaxed text-red-800 sm:text-base">
                                            Once submitted, your registration will be sent directly to the
                                            <strong class="font-bold">PSA Secretariat</strong>
                                            for review. Please enter a correct information for your
                                            <strong class="font-bold">PRC CPD Units</strong>.
                                        </p>

                                    </div>

                                </div>

                            </div>


                            {{-- reg details --}}
                            <div class="px-4 py-4 sm:px-7 sm:py-5">

                                <div class="mb-3">

                                    <h4 class="text-sm font-bold text-gray-900 sm:text-base">
                                        Registration Details
                                    </h4>

                                    <p class="mt-0.5 text-[10px] text-gray-400 sm:text-xs">
                                        Please verify that all information is correct.
                                    </p>

                                </div>


                                {{-- Details --}}
                                <div class="overflow-hidden rounded-xl border border-gray-200 sm:rounded-2xl">

                                    @foreach ([
                                        ['Full Name', $firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName, ''],
                                        ['Membership', 'Non-Member', ''],
                                        ['PRC Number', $prcNumber, 'font-mono'],
                                        ['Email', $email, ''],
                                        ['Contact Number', $contactNumber, ''],
                                        ['Hospital', $hospitalName, ''],
                                        ['Hospital Address', $hospitalAddress, ''],
                                        ['Country', $country, ''],
                                        ['Proof of Payment', $paymentProof ? 'Uploaded' : 'Not uploaded', ''],
                                    ] as [$label, $value, $extraClass])
                                        <div
                                            class="border-b border-gray-100 px-3.5 py-3 last:border-0
                                       sm:grid sm:grid-cols-[145px_1fr] sm:items-start sm:gap-4 sm:px-5 sm:py-3.5">

                                            {{-- Label --}}
                                            <dt
                                                class="text-[10px] font-medium uppercase tracking-wide text-gray-400 sm:text-xs sm:normal-case sm:tracking-normal">
                                                {{ $label }}
                                            </dt>

                                            {{-- Value --}}
                                            <dd
                                                class="mt-1 min-w-0 break-words text-left text-xs font-semibold text-gray-800 sm:mt-0 sm:text-right sm:text-sm {{ $extraClass }}">

                                                @if ($label === 'Proof of Payment')
                                                    @if ($value === 'Uploaded')
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-[10px] font-semibold text-green-700 sm:text-xs">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M5 13l4 4L19 7" />
                                                            </svg>

                                                            Uploaded
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-semibold text-red-600 sm:text-xs">
                                                            {{ $value }}
                                                        </span>
                                                    @endif
                                                @else
                                                    {{ $value !== '' && $value !== null ? $value : '—' }}
                                                @endif

                                            </dd>

                                        </div>
                                    @endforeach

                                </div>

                            </div>

                        </div>


                        {{-- footer --}}
                        <div class="shrink-0 border-t border-gray-100 bg-white px-4 py-3 sm:px-7 sm:py-4">

                            {{-- Mobile reminder --}}
                            <p class="mb-2.5 text-center text-[10px] leading-relaxed text-gray-400 sm:hidden">
                                Need to make a correction?
                                <span class="font-semibold text-gray-600">
                                    Go back and edit your information.
                                </span>
                            </p>


                            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3">

                                {{-- Confirm --}}
                                <button type="button" wire:click="submit" wire:loading.attr="disabled"
                                    wire:target="submit"
                                    class="order-1 w-full rounded-xl px-4 py-3 text-sm font-bold text-white shadow-md transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 sm:order-2 sm:w-auto sm:px-5"
                                    style="background-color:#000066;">

                                    <span wire:loading.remove wire:target="submit"
                                        class="flex items-center justify-center gap-2">

                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>

                                        Confirm & Submit

                                    </span>


                                    <span wire:loading wire:target="submit"
                                        class="flex items-center justify-center gap-2">

                                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>

                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 8 12 8v4z"></path>
                                        </svg>

                                        Submitting…

                                    </span>

                                </button>


                                {{-- Edit --}}
                                <button type="button" wire:click="cancelReview"
                                    class="order-2 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition active:scale-[0.98] hover:bg-gray-50 sm:order-1 sm:w-auto sm:px-5">

                                    <span class="flex items-center justify-center gap-2">

                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.232 5.232l3.536 3.536M9 11l-6 6v3h3l6-6m-3-3l6-6a2.121 2.121 0 013 3l-6 6" />
                                        </svg>

                                        Go Back & Edit

                                    </span>

                                </button>

                            </div>

                        </div>

                    </div>
                </div>

            @endif

        @endif
    </div>