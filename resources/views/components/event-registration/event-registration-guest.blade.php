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

        public bool   $submitted      = false;
        public string $registrationId = '';

        protected function rules(): array
        {
            return [
                'firstName'       => ['required', 'string', 'max:255'],
                'lastName'        => ['required', 'string', 'max:255'],
                'middleName'      => ['nullable', 'string', 'max:255'],

                'prcNumber'       => ['required', 'digits:7'],
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

        public function submit(): void
        {
            // Non-members are always fixed price / non-discounted, regardless of anything client-side.
            $this->discountType = 'non_disc';

            $this->validate();

            // Guests are still deduped by email — the psa_id itself is just a
            // sequential label and isn't unique-per-person the way member IDs are.
            $existing = Registration::where('prc_number', $this->prcNumber)
                ->where('psa_id', 'like', 'NM\_%')
                ->whereIn('status', [Registration::STATUS_PENDING, Registration::STATUS_APPROVED])
                ->exists();

            if ($existing) {
                    $this->addError('prcNumber', 'This PRC number is already registered.');
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

            <form wire:submit="submit">

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
                        <x-form.input label="PRC Number" hint="(7 digits)" name="prcNumber" wire:model="prcNumber" pattern="^\d{7}$"
                            placeholder="1234567" maxlength="7" inputmode="numeric" pattern-message="PRC number must be exactly 7 digits."/>

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
                    <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                        class="px-8 py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90 disabled:opacity-50
                        bg-[#000066]">
                        <span wire:loading.remove wire:target="submit">Submit Registration</span>
                        <span wire:loading wire:target="submit">Submitting…</span>
                    </button>
                </div>

            </form>
        @endif
    </div>