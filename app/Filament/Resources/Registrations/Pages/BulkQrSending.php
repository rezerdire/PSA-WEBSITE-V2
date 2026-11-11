<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\RegistrationQrBatch;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;

class BulkQrSending extends Page
{
    protected static string $resource = RegistrationResource::class;

    protected string $view = 'filament.resources.registrations.pages.bulk-qr-sending';

    protected string|Width|null $maxContentWidth = Width::Full;

    public ?int $batchId = null;

    public ?RegistrationQrBatch $batch = null;

    public function mount(?RegistrationQrBatch $batch = null): void
    {
        $this->batchId = $batch?->id ?? RegistrationQrBatch::query()->latest('id')->value('id');
        $this->refreshBatch();
    }

    public function refreshBatch(): void
    {
        $this->batch = $this->batchId
            ? RegistrationQrBatch::with([
                'recipients' => fn ($query) => $query->orderBy('position'),
            ])->find($this->batchId)
            : null;
    }

    public function progressPercent(): int
    {
        if (! $this->batch || $this->batch->total_count === 0) {
            return 0;
        }

        return min(100, (int) round(($this->batch->processed_count / $this->batch->total_count) * 100));
    }
}
