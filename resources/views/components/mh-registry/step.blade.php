@props(['episodeDate', 'episodeLocation', 'hospital', 'hospitalContact', 'mdName'])
<div class="mb-8">
    <x-event-registration.section-title title="Episode & Reporting Facility" />
    <p class="mb-6 text-sm text-slate-500">Enter the details of the MH episode and reporting clinician/facility.</p>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <x-form.input label="Date of MH Episode" type="date" name="episodeDate" wire:model="episodeDate" />
        <x-form.input label="Location of Episode" name="episodeLocation" wire:model="episodeLocation"
            placeholder="OR, Recovery Room, ICU, etc." />
        <x-form.input label="Hospital" name="hospital" wire:model="hospital" placeholder="Hospital name" />
        <x-form.input label="Contact Number of Hospital" name="hospitalContact" wire:model="hospitalContact"
            placeholder="e.g. 09171234567" />
        <div class="sm:col-span-2">
            <x-form.input label="Name of MD (Reporting Anesthesiologist)" name="mdName" wire:model="mdName"
                placeholder="Dr. Juan Dela Cruz" />
        </div>
    </div>
</div>