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
        $this->validate($this->rulesForStep($this->step));
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
<div x-data
    x-on:registry-submitted.window="$nextTick(() => document.getElementById('registry-success')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
    x-on:validation-failed.window="$nextTick(() => document.getElementById('error-summary')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
    @if ($submitted)
        <x-mh-registry.success :referenceNo="$referenceNo" :patientName="$patientName" :hospital="$hospital"
            :episodeDate="$episodeDate" :rawScore="$this->rawScore" :gradingRank="$this->gradingRank"
            :finalDisposition="$finalDisposition" />
    @else
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
            <x-mh-registry.page-header :step="$step" :totalSteps="$totalSteps" />
            <x-mh-registry.step-indicator :step="$step" :totalSteps="$totalSteps" />

            @if ($errors->any())
                <x-mh-registry.error-summary :errors="$errors" />
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
                                <div class="sm:col-span-2">
                                    <x-form.input label="Name" name="patientName" wire:model="patientName" />
                                </div>
                                <x-form.input label="Birthdate" type="date" name="birthdate" wire:model="birthdate" />
                                <x-form.input label="Birthplace" name="birthplace" wire:model="birthplace" />
                                <x-form.input label="Age" name="age" wire:model="age" inputmode="numeric" />
                                <x-mh-registry.select label="Sex" name="sex" required
                                    :options="['Male' => 'Male', 'Female' => 'Female']" />
                                <x-form.input label="Region / Province" name="region" wire:model="region" />
                                <div class="sm:col-span-2">
                                    <x-form.input label="Address" name="address" wire:model="address" />
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
                                <x-mh-registry.select label="Surgical Urgency" name="surgicalUrgency" required
                                    :options="['Elective' => 'Elective', 'Emergency' => 'Emergency']" />
                                <x-form.input label="Surgical Procedure" name="surgicalProcedure"
                                    wire:model="surgicalProcedure" />
                                <x-mh-registry.select label="Type of Anesthesia" name="anesthesiaType" required
                                    :options="['GETA' => 'GETA', 'GA-Mask' => 'GA-Mask', 'GA-LMA' => 'GA-LMA']" />
                                <x-form.input label="Volatile Anesthetic Used" name="volatileAgent" wire:model="volatileAgent"
                                    placeholder="e.g. Sevoflurane" />
                                <x-mh-registry.select label="Succinylcholine Given?" name="succinylcholine" required
                                    :options="['Yes' => 'Yes', 'No' => 'No']" />
                                <x-form.input label="Tachycardia (bpm)" name="tachycardiaBpm" wire:model="tachycardiaBpm"
                                    inputmode="numeric" />
                            </div>

                            <div class="mt-8">
                                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-500">Triggering
                                    Agents Used</label>
                                <x-mh-registry.checkbox-group name="triggeringAgents" :options="['Sevoflurane', 'Isoflurane', 'Desflurane', 'Halothane', 'Succinylcholine']" />
                            </div>

                            <div class="mt-8">
                                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-500">Signs &
                                    Symptoms Presentation</label>
                                <x-mh-registry.checkbox-group name="signsSymptoms" :options="['Tachycardia', 'Hypercapnia', 'Muscle rigidity', 'Hyperthermia', 'Tachypnea', 'Arrhythmia']" />
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
                                <x-mh-registry.select label="ICU Admission?" name="icuAdmission" required
                                    :options="['Yes' => 'Yes', 'No' => 'No']" />
                                <x-mh-registry.select label="Final Disposition" name="finalDisposition" required
                                    :options="['Improved' => 'Improved', 'Mortality' => 'Mortality', 'Morbidity' => 'Morbidity']" />
                            </div>

                            <div class="mt-8">
                                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-500">Cooling
                                    Measures Used</label>
                                <x-mh-registry.checkbox-group name="coolingMeasures" :options="['Ice packs', 'Cold IV fluids', 'Cooling blanket', 'Gastric/bladder lavage', 'Surface cooling fans']" />
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
                                        <x-mh-registry.weighted-checkbox model="rigidityGeneralized" points="15">
                                            Generalized rigidity
                                        </x-mh-registry.weighted-checkbox>
                                        <x-mh-registry.weighted-checkbox model="rigidityMasseter" points="15">
                                            Masseter spasm
                                        </x-mh-registry.weighted-checkbox>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process II</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Muscle Breakdown <span
                                            class="font-normal text-slate-400">(max 15 pts)</span></h3>
                                    <div class="mt-4 space-y-3">
                                        <x-mh-registry.weighted-checkbox model="ckElevated" points="15">
                                            CK &gt;20,000 with succinylcholine or &gt;10,000 without
                                        </x-mh-registry.weighted-checkbox>
                                        <x-mh-registry.weighted-checkbox model="colaColoredUrine" points="10">
                                            Cola-colored urine
                                        </x-mh-registry.weighted-checkbox>
                                        <x-mh-registry.weighted-checkbox model="myoglobinuria" points="5">
                                            Myoglobinuria / serum myoglobin
                                        </x-mh-registry.weighted-checkbox>
                                        <x-mh-registry.weighted-checkbox model="highPotassium" points="3">
                                            K+ &gt; 6 mEq/L
                                        </x-mh-registry.weighted-checkbox>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process III</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Respiratory Acidosis <span
                                            class="font-normal text-slate-400">(max 15 pts)</span></h3>
                                    <div class="mt-4">
                                        <x-mh-registry.weighted-checkbox model="respiratoryAcidosis" points="15">
                                            PETCO2 &gt;55 mmHg or PaCO2 &gt;60 mmHg
                                        </x-mh-registry.weighted-checkbox>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process IV</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Temperature Increase <span
                                            class="font-normal text-slate-400">(max 15 pts)</span></h3>
                                    <div class="mt-4">
                                        <x-mh-registry.weighted-checkbox model="rapidTempIncrease" points="15">
                                            Rapid, inappropriate increase (e.g. &gt;38.8°C)
                                        </x-mh-registry.weighted-checkbox>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process V</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Cardiac Involvement <span
                                            class="font-normal text-slate-400">(max 3 pts)</span></h3>
                                    <div class="mt-4">
                                        <x-mh-registry.weighted-checkbox model="cardiacInvolvement" points="3">
                                            Unexplained sinus tachycardia, VT, or VF
                                        </x-mh-registry.weighted-checkbox>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                                    <p class="text-xs font-black uppercase tracking-wider text-[#000066]">Process VI</p>
                                    <h3 class="mt-1 text-sm font-bold text-slate-800">Family History <span
                                            class="font-normal text-slate-400">(max 5 pts)</span></h3>
                                    <div class="mt-4">
                                        <x-mh-registry.weighted-checkbox model="familyHistory" points="5">
                                            Inherited pattern
                                        </x-mh-registry.weighted-checkbox>
                                    </div>
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
                                    <x-mh-registry.select label="Survived or Demise" name="outcome" required
                                        :options="['Survived' => 'Survived', 'Demise' => 'Demise']" />
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
                                        <x-mh-registry.select :label="$label" :name="$prop" required
                                            :options="['Yes' => 'Yes', 'No' => 'No']" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Navigation --}}
                <x-mh-registry.form-nav :step="$step" :totalSteps="$totalSteps" />
            </form>
        </div>
    @endif
</div>