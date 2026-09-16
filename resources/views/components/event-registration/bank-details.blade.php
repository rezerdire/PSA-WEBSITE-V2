<?php

use Livewire\Component;

new class extends Component {
    public string $bankName;
    public string $accountNumber;
    public string $accountName;
    public ?string $logo;
    public string $label;
    public array $fees;

    public function mount(string $bankName, string $accountNumber, string $accountName, ?string $logo = null, string $label = 'Bank Details', array $fees = []): void
    {
        $this->bankName = $bankName;
        $this->accountNumber = $accountNumber;
        $this->accountName = $accountName;
        $this->logo = $logo;
        $this->label = $label;
        $this->fees = $fees;
    }
};
?>

<div class="rounded-xl border border-red-300 bg-red-50 px-5 py-4 mb-3">

    <div class="flex items-center gap-2 mb-3">
        <span class="text-xs font-bold uppercase tracking-wide" style="color: #cc0000;">{{ $label }}</span>
        <span class="h-px flex-1" style="background-color: #f3c2c2;"></span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm">
        <div>
            <p class="text-[10px] text-red-400 font-medium uppercase tracking-wide mb-1">Bank</p>
            @if ($logo)
                <img src="{{ asset($logo) }}" alt="{{ $bankName }}" class="w-10 h-10 object-contain">
            @else
                <p class="font-semibold text-gray-800">{{ $bankName }}</p>
            @endif
        </div>
        <div>
            <p class="text-[10px] text-red-400 font-medium uppercase tracking-wide">Account Number</p>
            <p class="font-mono font-semibold text-gray-800">{{ $accountNumber ?: '—' }}</p>
        </div>
        <div>
            <p class="text-[10px] text-red-400 font-medium uppercase tracking-wide">Account Name</p>
            <p class="font-semibold text-gray-800">{{ $accountName ?: '—' }}</p>
        </div>
        @if (!empty($fees))
            @foreach ($fees as $feeLabel => $amount)
                <div>
                    <p class="text-[10px] text-red-400 font-medium uppercase tracking-wide">{{ $feeLabel }}</p>
                    <p class="font-semibold text-gray-800"><strong>₱{{ number_format((float) $amount, 2) }}</strong></p>
                </div>
            @endforeach
        @endif
    </div>




</div>
