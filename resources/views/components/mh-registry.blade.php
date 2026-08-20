<?php

use App\Models\MhRegistryEntry;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

new class extends Component {

    use WithFileUploads;

    // ==== Wizard control ====
    public int $step = 1;
    public int $totalSteps = 6;

    // ==== Header ====
    public string $episodeDate = '';
    public string $episodeLocation = '';
    public string $hospital = '';
    public string $hospitalContact = '';
    public string $mdName = '';

    // ==== Part 1A. Patient Demographics ====
    public string $patientName = '';
    public string $birthdate = '';
    public string $birthplace = '';
    public string $age = '';
    public string $sex = '';
    public string $region = '';
    public string $address = '';
    public string $contactNumber = '';
    public string $ethnicity = '';
    public string $height = '';
    public string $weight = '';

    // ==== Part 1B. Clinical Event Data ====
    public string $surgicalUrgency = ''; // Elective / Emergency
    public string $surgicalProcedure = '';
    public string $anesthesiaType = ''; // GETA, GA-Mask, GA-LMA
    public array $triggeringAgents = [];
    public string $volatileAgent = '';
    public string $succinylcholine = ''; // Yes/No
    public array $signsSymptoms = [];
    public string $tachycardiaBpm = '';
    public string $hypercapnia = '';
    public string $muscleRigidity = '';
    public string $hyperthermia = '';

    // ==== Part 2C. Management ====
    public string $timeToRecognition = '';
    public string $dantroleneTime = '';
    public string $dantroleneLoadDose = '';
    public string $dantroleneTotalDose = '';
    public string $dantroleneDuration = '';
    public array $coolingMeasures = [];
    public string $icuAdmission = ''; // Yes/No
    public string $finalDisposition = ''; // Improved/Mortality/Morbidity

    // ==== D. Diagnostics — Clinical Grading Scale ====
    public bool $rigidityGeneralized = false; // 15
    public bool $rigidityMasseter = false; // 15

    public bool $ckElevated = false; // 15
    public bool $colaColoredUrine = false; // 10
    public bool $myoglobinuria = false; // 5
    public bool $highPotassium = false; // 3

    public bool $respiratoryAcidosis = false; // 15

    public bool $rapidTempIncrease = false; // 15

    public bool $cardiacInvolvement = false; // 3

    public bool $familyHistory = false; // 5

    public string $geneticTesting = '';
    public string $muscleBiopsy = '';

    // ==== E. Outcomes ====
    public string $outcome = ''; // Survived / Demise
    public bool $compRenalFailure = false;
    public bool $compDic = false;
    public bool $compNeurologic = false;
    public string $lengthHospitalStay = '';
    public string $lengthIcuStay = '';
    public string $longTermSequelae = '';

    // ==== F. Facility Readiness ====
    public string $dantroleneAvailable = ''; // Yes/No
    public string $mhProtocolPresent = ''; // Yes/No
    public string $mhCartAvailable = ''; // Yes/No
    public string $staffTrainingDone = ''; // Yes/No

    public bool $submitted = false;
    public string $referenceNo = '';

    protected const STEP_FIELDS = [
        1 => ['episodeDate', 'episodeLocation', 'hospital', 'hospitalContact', 'mdName'],
        2 => ['patientName', 'birthdate', 'birthplace', 'age', 'sex', 'region', 'address', 'contactNumber', 'ethnicity', 'height', 'weight'],
        3 => ['surgicalUrgency', 'surgicalProcedure', 'anesthesiaType', 'triggeringAgents', 'volatileAgent', 'succinylcholine', 'signsSymptoms', 'tachycardiaBpm', 'hypercapnia', 'muscleRigidity', 'hyperthermia'],
        4 => ['timeToRecognition', 'dantroleneTime', 'dantroleneLoadDose', 'dantroleneTotalDose', 'dantroleneDuration', 'coolingMeasures', 'icuAdmission', 'finalDisposition'],
        5 => ['geneticTesting', 'muscleBiopsy', 'outcome', 'lengthHospitalStay', 'lengthIcuStay', 'longTermSequelae'],
        6 => ['dantroleneAvailable', 'mhProtocolPresent', 'mhCartAvailable', 'staffTrainingDone'],
    ];

    // ==== Computed grading scale score ====
    public function getRawScoreProperty(): int
    {
        $score = 0;
        $score += $this->rigidityGeneralized || $this->rigidityMasseter ? 15 : 0;

        $processII = 0;
        if ($this->ckElevated)
            $processII = max($processII, 15);
        if ($this->colaColoredUrine)
            $processII = max($processII, 10);
        if ($this->myoglobinuria)
            $processII = max($processII, 5);
        if ($this->highPotassium)
            $processII = max($processII, 3);
        $score += $processII;

        $score += $this->respiratoryAcidosis ? 15 : 0;
        $score += $this->rapidTempIncrease ? 15 : 0;
        $score += $this->cardiacInvolvement ? 3 : 0;
        $score += $this->familyHistory ? 5 : 0;

        return $score;
    }

    public function getGradingRankProperty(): array
    {
        $score = $this->rawScore;

        return match (true) {
            $score === 0 => ['rank' => 1, 'label' => 'Almost never'],
            $score >= 3 && $score <= 9 => ['rank' => 2, 'label' => 'Unlikely'],
            $score >= 10 && $score <= 19 => ['rank' => 3, 'label' => 'Somewhat less than likely'],
            $score >= 20 && $score <= 34 => ['rank' => 4, 'label' => 'Somewhat greater than likely'],
            $score >= 35 && $score <= 49 => ['rank' => 5, 'label' => 'Very likely'],
            $score >= 50 => ['rank' => 6, 'label' => 'Almost certain'],
            default => ['rank' => 1, 'label' => 'Almost never'],
        };
    }

    public function nextStep(): void
    {
        $rules = $this->rulesForStep($this->step);
        $this->validate($rules);

        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function goToStep(int $target): void
    {
        if ($target < $this->step) {
            $this->step = $target;
            return;
        }

        for ($s = $this->step; $s < $target; $s++) {
            $this->validate($this->rulesForStep($s));
        }
        $this->step = $target;
    }

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'episodeDate' => ['required', 'date'],
                'episodeLocation' => ['required', 'string', 'max:255'],
                'hospital' => ['required', 'string', 'max:255'],
                'hospitalContact' => ['required', 'string', 'max:50'],
                'mdName' => ['required', 'string', 'max:255'],
            ],
            2 => [
                'patientName' => ['required', 'string', 'max:255'],
                'birthdate' => ['nullable', 'date'],
                'birthplace' => ['nullable', 'string', 'max:255'],
                'age' => ['required', 'integer', 'min:0', 'max:130'],
                'sex' => ['required', Rule::in(['Male', 'Female'])],
                'region' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
                'contactNumber' => ['nullable', 'string', 'max:20'],
                'ethnicity' => ['nullable', 'string', 'max:255'],
                'height' => ['nullable', 'numeric'],
                'weight' => ['nullable', 'numeric'],
            ],
            3 => [
                'surgicalUrgency' => ['required', Rule::in(['Elective', 'Emergency'])],
                'surgicalProcedure' => ['required', 'string', 'max:255'],
                'anesthesiaType' => ['required', Rule::in(['GETA', 'GA-Mask', 'GA-LMA'])],
                'triggeringAgents' => ['nullable', 'array'],
                'volatileAgent' => ['nullable', 'string', 'max:255'],
                'succinylcholine' => ['required', Rule::in(['Yes', 'No'])],
                'signsSymptoms' => ['nullable', 'array'],
                'tachycardiaBpm' => ['nullable', 'integer', 'min:0'],
            ],
            4 => [
                'timeToRecognition' => ['nullable', 'string', 'max:255'],
                'dantroleneTime' => ['nullable', 'string', 'max:255'],
                'dantroleneLoadDose' => ['nullable', 'string', 'max:255'],
                'dantroleneTotalDose' => ['nullable', 'string', 'max:255'],
                'dantroleneDuration' => ['nullable', 'string', 'max:255'],
                'coolingMeasures' => ['nullable', 'array'],
                'icuAdmission' => ['required', Rule::in(['Yes', 'No'])],
                'finalDisposition' => ['required', Rule::in(['Improved', 'Mortality', 'Morbidity'])],
            ],
            5 => [
                'geneticTesting' => ['nullable', 'string', 'max:255'],
                'muscleBiopsy' => ['nullable', 'string', 'max:255'],
                'outcome' => ['required', Rule::in(['Survived', 'Demise'])],
                'lengthHospitalStay' => ['nullable', 'string', 'max:100'],
                'lengthIcuStay' => ['nullable', 'string', 'max:100'],
                'longTermSequelae' => ['nullable', 'string', 'max:1000'],
            ],
            6 => [
                'dantroleneAvailable' => ['required', Rule::in(['Yes', 'No'])],
                'mhProtocolPresent' => ['required', Rule::in(['Yes', 'No'])],
                'mhCartAvailable' => ['required', Rule::in(['Yes', 'No'])],
                'staffTrainingDone' => ['required', Rule::in(['Yes', 'No'])],
            ],
            default => [],
        };
    }

    public function submit(): void
    {
        $allRules = [];
        foreach (range(1, $this->totalSteps) as $s) {
            $allRules = array_merge($allRules, $this->rulesForStep($s));
        }
        $this->validate($allRules);

        $entry = MhRegistryEntry::create([
            'episode_date' => $this->episodeDate,
            'episode_location' => $this->episodeLocation,
            'hospital' => $this->hospital,
            'hospital_contact' => $this->hospitalContact,
            'md_name' => $this->mdName,

            'patient_name' => $this->patientName,
            'birthdate' => $this->birthdate ?: null,
            'birthplace' => $this->birthplace,
            'age' => $this->age,
            'sex' => $this->sex,
            'region' => $this->region,
            'address' => $this->address,
            'contact_number' => $this->contactNumber,
            'ethnicity' => $this->ethnicity,
            'height' => $this->height ?: null,
            'weight' => $this->weight ?: null,
            'bmi' => $this->computeBmi(),

            'surgical_urgency' => $this->surgicalUrgency,
            'surgical_procedure' => $this->surgicalProcedure,
            'anesthesia_type' => $this->anesthesiaType,
            'triggering_agents' => $this->triggeringAgents,
            'volatile_agent' => $this->volatileAgent,
            'succinylcholine' => $this->succinylcholine === 'Yes',
            'signs_symptoms' => $this->signsSymptoms,
            'tachycardia_bpm' => $this->tachycardiaBpm ?: null,
            'hypercapnia' => $this->hypercapnia,
            'muscle_rigidity' => $this->muscleRigidity,
            'hyperthermia' => $this->hyperthermia,

            'time_to_recognition' => $this->timeToRecognition,
            'dantrolene_time' => $this->dantroleneTime,
            'dantrolene_load_dose' => $this->dantroleneLoadDose,
            'dantrolene_total_dose' => $this->dantroleneTotalDose,
            'dantrolene_duration' => $this->dantroleneDuration,
            'cooling_measures' => $this->coolingMeasures,
            'icu_admission' => $this->icuAdmission === 'Yes',
            'final_disposition' => $this->finalDisposition,

            'grading_raw_score' => $this->rawScore,
            'grading_rank' => $this->gradingRank['rank'],
            'grading_rank_label' => $this->gradingRank['label'],
            'genetic_testing' => $this->geneticTesting,
            'muscle_biopsy' => $this->muscleBiopsy,

            'outcome' => $this->outcome,
            'comp_renal_failure' => $this->compRenalFailure,
            'comp_dic' => $this->compDic,
            'comp_neurologic' => $this->compNeurologic,
            'length_hospital_stay' => $this->lengthHospitalStay,
            'length_icu_stay' => $this->lengthIcuStay,
            'long_term_sequelae' => $this->longTermSequelae,

            'dantrolene_available' => $this->dantroleneAvailable === 'Yes',
            'mh_protocol_present' => $this->mhProtocolPresent === 'Yes',
            'mh_cart_available' => $this->mhCartAvailable === 'Yes',
            'staff_training_done' => $this->staffTrainingDone === 'Yes',
        ]);

        $this->referenceNo = 'MH-' . str_pad((string) $entry->id, 5, '0', STR_PAD_LEFT);
        $this->submitted = true;

        $this->dispatch('registry-submitted');
    }

    protected function computeBmi(): ?float
    {
        if (!$this->height || !$this->weight) {
            return null;
        }

        $heightM = ((float) $this->height) / 100;
        if ($heightM <= 0) {
            return null;
        }

        return round(((float) $this->weight) / ($heightM ** 2), 1);
    }

};
?>

{{-- FRONTEND --}}
<div class="min-h-screen bg-slate-50" x-data
    x-on:registry-submitted.window="$nextTick(() => document.getElementById('registry-success')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
    x-on:validation-failed.window="$nextTick(() => document.getElementById('error-summary')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
    @if ($submitted)
        <div class="max-w-3xl mx-auto px-4 py-10 sm:px-6 lg:px-8" id="registry-success">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50">
                <div class="bg-[#000066] px-6 py-8 text-center sm:px-10">
                    <div
                        class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-white/15 ring-8 ring-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-blue-100">National MH Registry</p>
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">Report Submitted Successfully</h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-blue-100">
                        The MH episode has been recorded in the National Malignant Hyperthermia Registry.
                    </p>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="mb-6 rounded-2xl border border-blue-100 bg-blue-50/60 p-5">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Reference Number</p>
                        <p class="mt-1 text-2xl font-black tracking-wide text-[#000066]">{{ $referenceNo }}</p>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200">
                        <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Report Summary</h3>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach ([
                                    ['Reference No.', $referenceNo],
                                    ['Patient', $patientName],
                                    ['Hospital', $hospital],
                                    ['Episode Date', $episodeDate],
                                    ['Grading Score', $rawScore . ' pts — Rank ' . $gradingRank['rank'] . ' (' . $gradingRank['label'] . ')'],
                                    ['Final Disposition', $finalDisposition],
                                ] as [$label, $value])
                                <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-[180px_1fr] sm:gap-4">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</span>
                                    <span class="text-sm font-semibold text-slate-700">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <a href="{{ url('/') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-[#000066] px-7 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#00004d] focus:outline-none focus:ring-4 focus:ring-blue-100">
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
            {{-- Page header --}}
            <div class="mb-8 overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500">
                <div class="px-6 py-7 sm:px-8 lg:px-10">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div
                                class="mb-2 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.15em] text-blue-100">
                                <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                National Registry
                            </div>
                            <h1 class="text-2xl font-black tracking-tight text-white sm:text-3xl">
                                Malignant Hyperthermia Episode Report
                            </h1>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                                Philippine Society of Anesthesiologists - National Malignant Hyperthermia Committee
                            </p>
                        </div>

                        <div class="shrink-0 rounded-2xl bg-white/10 px-5 py-4 text-left sm:text-right">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-blue-200">Progress</p>
                            <p class="mt-1 text-xl font-black text-white">{{ $step }} <span
                                    class="text-sm font-medium text-blue-200">/ {{ $totalSteps }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step indicator --}}
            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="hidden items-center md:flex">
                    @foreach ([
                            1 => 'Header',
                            2 => 'Demographics',
                            3 => 'Clinical Event',
                            4 => 'Management',
                            5 => 'Diagnostics',
                            6 => 'Facility',
                        ] as $num => $label)
                        <div class="flex flex-1 items-center">
                            <button type="button" wire:click="goToStep({{ $num }})"
                                class="group flex min-w-0 items-center gap-3 text-left">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-xs font-black transition
                                                                    {{ $step > $num ? 'border-[#000066] bg-[#000066] text-white' : ($step === $num ? 'border-[#000066] bg-blue-50 text-[#000066]' : 'border-slate-200 bg-white text-slate-400') }}">
                                    @if ($step > $num)
                                        ✓
                                    @else
                                        {{ $num }}
                                    @endif
                                </span>
                                <span class="hidden min-w-0 lg:block">
                                    <span
                                        class="block text-[10px] font-bold uppercase tracking-wider {{ $step === $num ? 'text-[#000066]' : 'text-slate-400' }}">Step
                                        {{ $num }}</span>
                                    <span
                                        class="block truncate text-xs font-semibold {{ $step === $num ? 'text-slate-800' : 'text-slate-500' }}">{{ $label }}</span>
                                </span>
                            </button>
                            @if ($num < $totalSteps)
                                <div class="mx-3 h-px flex-1 {{ $step > $num ? 'bg-[#000066]' : 'bg-slate-200' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between md:hidden">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current section</p>
                        <p class="mt-1 text-sm font-bold text-slate-800">
                            {{ [
            1 => 'Episode & Reporting Facility',
            2 => 'Patient Demographics',
            3 => 'Clinical Event Data',
            4 => 'Management',
            5 => 'Diagnostics & Outcome',
            6 => 'Facility Readiness',
        ][$step] }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-black text-[#000066]">{{ $step }}/{{ $totalSteps }}</p>
                    </div>
                </div>

                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-[#000066] transition-all duration-300"
                        style="width: {{ (($step - 1) / ($totalSteps - 1)) * 100 }}%"></div>
                </div>
            </div>

            {{-- Error summary --}}
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5" id="error-summary">
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">!
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-800">Please review the following fields</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li class="text-xs leading-5 text-red-700">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit="submit">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 lg:p-9">

                    {{-- STEP 1 --}}
                    @if ($step === 1)
                        <div class="mb-8">
                            <x-event-registration.section-title title="Episode & Reporting Facility" />
                            <p class="mb-6 text-sm text-slate-500">Enter the details of the MH episode and reporting
                                clinician/facility.</p>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <x-form.input label="Date of MH Episode" type="date" name="episodeDate"
                                    wire:model="episodeDate" />
                                <x-form.input label="Location of Episode" name="episodeLocation" wire:model="episodeLocation"
                                    placeholder="OR, Recovery Room, ICU, etc." />
                                <x-form.input label="Hospital" name="hospital" wire:model="hospital"
                                    placeholder="Hospital name" />
                                <x-form.input label="Contact Number of Hospital" name="hospitalContact"
                                    wire:model="hospitalContact" placeholder="e.g. 09171234567" />
                                <div class="sm:col-span-2">
                                    <x-form.input label="Name of MD (Reporting Anesthesiologist)" name="mdName"
                                        wire:model="mdName" placeholder="Dr. Juan Dela Cruz" />
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- STEP 2 --}}
                    @if ($step === 2)
                        <div class="mb-8">
                            <x-event-registration.section-title title="A. Patient Demographics" />
                            <p class="mb-6 text-sm text-slate-500">Provide the patient's demographic and basic physical
                                information.</p>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                                <div class="sm:col-span-2"><x-form.input label="Name" name="patientName"
                                        wire:model="patientName" /></div>
                                <x-form.input label="Birthdate" type="date" name="birthdate" wire:model="birthdate" />
                                <x-form.input label="Birthplace" name="birthplace" wire:model="birthplace" />
                                <x-form.input label="Age" name="age" wire:model="age" inputmode="numeric" />
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Sex <span
                                            class="text-red-500">*</span></label>
                                    <select wire:model="sex"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm transition focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    @error('sex')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <x-form.input label="Region / Province" name="region" wire:model="region" />
                                <div class="sm:col-span-2"><x-form.input label="Address" name="address" wire:model="address" />
                                </div>
                                <x-form.input label="Contact Number" name="contactNumber" wire:model="contactNumber" />
                                <x-form.input label="Ethnicity" name="ethnicity" wire:model="ethnicity" />
                                <x-form.input label="Height (cm)" name="height" wire:model="height" inputmode="decimal" />
                                <x-form.input label="Weight (kg)" name="weight" wire:model="weight" inputmode="decimal" />
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">BMI <span
                                            class="font-normal text-slate-400">(auto-computed)</span></label>
                                    <div
                                        class="flex min-h-[46px] items-center rounded-xl border border-blue-100 bg-blue-50 px-4 text-sm font-bold text-[#000066]">
                                        {{ $this->computeBmi() ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- STEP 3 --}}
                    @if ($step === 3)
                        <div class="mb-8">
                            <x-event-registration.section-title title="B. Clinical Event Data" />
                            <p class="mb-6 text-sm text-slate-500">Document the procedure, anesthetic exposure, triggering
                                agents, and presenting signs.</p>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Surgical Urgency <span
                                            class="text-red-500">*</span></label>
                                    <select wire:model="surgicalUrgency"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50">
                                        <option value="">Select</option>
                                        <option value="Elective">Elective</option>
                                        <option value="Emergency">Emergency</option>
                                    </select>
                                </div>
                                <x-form.input label="Surgical Procedure" name="surgicalProcedure"
                                    wire:model="surgicalProcedure" />
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Type of Anesthesia <span
                                            class="text-red-500">*</span></label>
                                    <select wire:model="anesthesiaType"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50">
                                        <option value="">Select</option>
                                        <option value="GETA">GETA</option>
                                        <option value="GA-Mask">GA-Mask</option>
                                        <option value="GA-LMA">GA-LMA</option>
                                    </select>
                                </div>
                                <x-form.input label="Volatile Anesthetic Used" name="volatileAgent" wire:model="volatileAgent"
                                    placeholder="e.g. Sevoflurane" />
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Succinylcholine Given? <span
                                            class="text-red-500">*</span></label>
                                    <select wire:model="succinylcholine"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50">
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <x-form.input label="Tachycardia (bpm)" name="tachycardiaBpm" wire:model="tachycardiaBpm"
                                    inputmode="numeric" />
                            </div>

                            <div class="mt-8">
                                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-500">Triggering
                                    Agents Used</label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach (['Sevoflurane', 'Isoflurane', 'Desflurane', 'Halothane', 'Succinylcholine'] as $agent)
                                        <label
                                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50/50">
                                            <input type="checkbox" value="{{ $agent }}" wire:model="triggeringAgents"
                                                class="h-4 w-4 rounded border-slate-300 text-[#000066] focus:ring-[#000066]">
                                            {{ $agent }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-8">
                                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-500">Signs &
                                    Symptoms Presentation</label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach (['Tachycardia', 'Hypercapnia', 'Muscle rigidity', 'Hyperthermia', 'Tachypnea', 'Arrhythmia'] as $sign)
                                        <label
                                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50/50">
                                            <input type="checkbox" value="{{ $sign }}" wire:model="signsSymptoms"
                                                class="h-4 w-4 rounded border-slate-300 text-[#000066] focus:ring-[#000066]">
                                            {{ $sign }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
                                <x-form.input label="Hypercapnia (details)" name="hypercapnia" wire:model="hypercapnia" />
                                <x-form.input label="Muscle Rigidity (details)" name="muscleRigidity"
                                    wire:model="muscleRigidity" />
                                <x-form.input label="Hyperthermia (peak temp)" name="hyperthermia" wire:model="hyperthermia"
                                    placeholder="e.g. 40.2°C" />
                            </div>
                        </div>
                    @endif

                    {{-- STEP 4 --}}
                    @if ($step === 4)
                        <div class="mb-8">
                            <x-event-registration.section-title title="C. Management" />
                            <p class="mb-6 text-sm text-slate-500">Record recognition timing, dantrolene administration,
                                cooling, and immediate disposition.</p>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <x-form.input label="Time to Recognition" name="timeToRecognition"
                                    wire:model="timeToRecognition" placeholder="e.g. 12 min from induction" />
                                <x-form.input label="Time of Dantrolene Loading Dose" name="dantroleneTime"
                                    wire:model="dantroleneTime" />
                                <x-form.input label="Dantrolene Loading Dose" name="dantroleneLoadDose"
                                    wire:model="dantroleneLoadDose" placeholder="mg/kg" />
                                <x-form.input label="Total Dantrolene Dose Given" name="dantroleneTotalDose"
                                    wire:model="dantroleneTotalDose" placeholder="mg/kg" />
                                <x-form.input label="Duration of Dantrolene Administration" name="dantroleneDuration"
                                    wire:model="dantroleneDuration" />
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">ICU Admission? <span
                                            class="text-red-500">*</span></label>
                                    <select wire:model="icuAdmission"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50">
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Final Disposition <span
                                            class="text-red-500">*</span></label>
                                    <select wire:model="finalDisposition"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50">
                                        <option value="">Select</option>
                                        <option value="Improved">Improved</option>
                                        <option value="Mortality">Mortality</option>
                                        <option value="Morbidity">Morbidity</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-8">
                                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-500">Cooling
                                    Measures Used</label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach (['Ice packs', 'Cold IV fluids', 'Cooling blanket', 'Gastric/bladder lavage', 'Surface cooling fans'] as $measure)
                                        <label
                                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50/50">
                                            <input type="checkbox" value="{{ $measure }}" wire:model="coolingMeasures"
                                                class="h-4 w-4 rounded border-slate-300 text-[#000066] focus:ring-[#000066]">
                                            {{ $measure }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- STEP 5 --}}
                    @if ($step === 5)
                        <div class="mb-8">
                            <x-event-registration.section-title title="D. Diagnostics — Clinical Grading Scale" />
                            <p class="mb-6 text-sm text-slate-500">Select the findings that apply. The clinical grading score
                                updates automatically.</p>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process I</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Rigidity <span
                                            class="font-normal text-slate-400">(max 15 pts)</span></h3>
                                    <div class="mt-4 space-y-3">
                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                                type="checkbox" wire:model.live="rigidityGeneralized"
                                                class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>Generalized rigidity
                                                <b>(15)</b></span></label>
                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                                type="checkbox" wire:model.live="rigidityMasseter"
                                                class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>Masseter spasm
                                                <b>(15)</b></span></label>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process II</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Muscle Breakdown <span
                                            class="font-normal text-slate-400">(max 15 pts)</span></h3>
                                    <div class="mt-4 space-y-3">
                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                                type="checkbox" wire:model.live="ckElevated"
                                                class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>CK &gt;20,000 with
                                                succinylcholine or &gt;10,000 without <b>(15)</b></span></label>
                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                                type="checkbox" wire:model.live="colaColoredUrine"
                                                class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>Cola-colored urine
                                                <b>(10)</b></span></label>
                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                                type="checkbox" wire:model.live="myoglobinuria"
                                                class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>Myoglobinuria / serum
                                                myoglobin <b>(5)</b></span></label>
                                        <label
                                            class="flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                                type="checkbox" wire:model.live="highPotassium"
                                                class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>K+ &gt; 6 mEq/L
                                                <b>(3)</b></span></label>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process III</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Respiratory Acidosis <span
                                            class="font-normal text-slate-400">(max 15 pts)</span></h3>
                                    <label
                                        class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                            type="checkbox" wire:model.live="respiratoryAcidosis"
                                            class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>PETCO2 &gt;55 mmHg or PaCO2
                                            &gt;60 mmHg <b>(15)</b></span></label>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process IV</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Temperature Increase <span
                                            class="font-normal text-slate-400">(max 15 pts)</span></h3>
                                    <label
                                        class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                            type="checkbox" wire:model.live="rapidTempIncrease"
                                            class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>Rapid, inappropriate increase
                                            (e.g. &gt;38.8°C) <b>(15)</b></span></label>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process V</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Cardiac Involvement <span
                                            class="font-normal text-slate-400">(max 3 pts)</span></h3>
                                    <label
                                        class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                            type="checkbox" wire:model.live="cardiacInvolvement"
                                            class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>Unexplained sinus tachycardia,
                                            VT, or VF <b>(3)</b></span></label>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process VI</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Family History <span
                                            class="font-normal text-slate-400">(max 5 pts)</span></h3>
                                    <label
                                        class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl bg-white p-3 text-sm text-slate-700 shadow-sm"><input
                                            type="checkbox" wire:model.live="familyHistory"
                                            class="mt-0.5 h-4 w-4 rounded text-[#000066]"> <span>Inherited pattern
                                            <b>(5)</b></span></label>
                                </div>
                            </div>

                            <div class="mt-6 rounded-2xl bg-[#000066] p-5 text-white shadow-lg shadow-blue-950/10">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-200">Clinical
                                            Grading Score</p>
                                        <p class="mt-1 text-3xl font-black">{{ $this->rawScore }} <span
                                                class="text-sm font-semibold text-blue-200">points</span></p>
                                    </div>
                                    <div class="rounded-xl bg-white/10 px-4 py-3 sm:text-right">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Likelihood Rank
                                        </p>
                                        <p class="mt-1 text-sm font-black">Rank {{ $this->gradingRank['rank'] }} —
                                            {{ $this->gradingRank['label'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <x-form.input label="Genetic Testing (RYR1, CACNA1S)" name="geneticTesting"
                                    wire:model="geneticTesting" placeholder="Result if available" />
                                <x-form.input label="Muscle Biopsy / IVCT" name="muscleBiopsy" wire:model="muscleBiopsy"
                                    placeholder="Result if available" />
                            </div>

                            <div class="mt-8">
                                <x-event-registration.section-title title="E. Outcomes" />
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold text-slate-600">Survived or Demise <span
                                                class="text-red-500">*</span></label>
                                        <select wire:model="outcome"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50">
                                            <option value="">Select</option>
                                            <option value="Survived">Survived</option>
                                            <option value="Demise">Demise</option>
                                        </select>
                                    </div>
                                    <x-form.input label="Length of Hospital Stay" name="lengthHospitalStay"
                                        wire:model="lengthHospitalStay" placeholder="e.g. 10 days" />
                                    <x-form.input label="Length of ICU Stay" name="lengthIcuStay" wire:model="lengthIcuStay"
                                        placeholder="e.g. 4 days" />
                                </div>

                                <div class="mt-6">
                                    <label
                                        class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-500">Complications</label>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                        <label
                                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm font-medium text-slate-700"><input
                                                type="checkbox" wire:model="compRenalFailure"
                                                class="h-4 w-4 rounded text-[#000066]"> Renal failure</label>
                                        <label
                                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm font-medium text-slate-700"><input
                                                type="checkbox" wire:model="compDic" class="h-4 w-4 rounded text-[#000066]">
                                            Disseminated Intravascular Coagulopathy</label>
                                        <label
                                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm font-medium text-slate-700"><input
                                                type="checkbox" wire:model="compNeurologic"
                                                class="h-4 w-4 rounded text-[#000066]"> Neurologic injury</label>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Long-term Sequelae (if
                                        any)</label>
                                    <textarea wire:model="longTermSequelae" rows="4"
                                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm shadow-sm transition focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50"></textarea>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- STEP 6 --}}
                    @if ($step === 6)
                        <div class="mb-8">
                            <x-event-registration.section-title title="F. Facility Readiness" />
                            <p class="mb-6 text-sm text-slate-500">Document the facility's readiness to recognize and manage a
                                malignant hyperthermia event.</p>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                @foreach ([
                                        ['dantroleneAvailable', 'Dantrolene Availability'],
                                        ['mhProtocolPresent', 'MH Protocol Present'],
                                        ['mhCartAvailable', 'MH Cart Available'],
                                        ['staffTrainingDone', 'Staff MH Training Conducted'],
                                    ] as [$prop, $label])
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                        <label class="mb-2 block text-sm font-bold text-slate-700">{{ $label }} <span
                                                class="text-red-500">*</span></label>
                                        <select wire:model="{{ $prop }}"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#000066] focus:outline-none focus:ring-4 focus:ring-blue-50">
                                            <option value="">Select</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Navigation --}}
                <div
                    class="mt-5 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <button type="button" wire:click="prevStep"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100 {{ $step === 1 ? 'invisible' : '' }}">
                        ← <span>Back</span>
                    </button>

                    <div class="text-center">
                        <p class="hidden text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:block">
                            Step {{ $step }} of {{ $totalSteps }}
                        </p>
                        <p class="text-xs text-slate-400">Fields marked <span class="text-red-500">*</span> are required</p>
                    </div>

                    @if ($step < $totalSteps)
                        <button type="button" wire:click="nextStep"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#000066] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#00004d] focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <span>Next</span> →
                        </button>
                    @else
                        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#000066] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#00004d] focus:outline-none focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading.remove wire:target="submit">Submit Report</span>
                            <span wire:loading wire:target="submit">Submitting…</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    @endif
</div>