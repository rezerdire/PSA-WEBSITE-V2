<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Booth Check-in')] class extends Component {
    public function verify(): void
    {
        abort(403, 'Booth check-in has ended.');
    }
};
?>

<div class="w-full">
    <x-booth.page-header eyebrow="Event Booth Check-in" title="Booth Check-in Has Ended"
        description="The QR booth activity has ended. We will now proceed to the raffle." />

    <div class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-md sm:p-8">
        <p class="text-sm leading-6 text-slate-600">Thank you for participating in the booth activity.</p>
        <a href="{{ route('booth.checker') }}" wire:navigate
            class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200 sm:w-auto">
            View my booth visits
        </a>
    </div>
</div>
