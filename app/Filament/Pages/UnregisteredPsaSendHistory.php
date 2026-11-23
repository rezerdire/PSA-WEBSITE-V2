<?php

namespace App\Filament\Pages;

use App\Models\UnregisteredPsaSendBatch;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class UnregisteredPsaSendHistory extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'PSA Email Send History';

    protected static ?string $title = 'Unregistered PSA Email Send History';

    protected string $view = 'filament.pages.unregistered-psa-send-history';

    protected string|Width|null $maxContentWidth = Width::Full;

    public ?int $batchId = null;

    public ?UnregisteredPsaSendBatch $batch = null;

    public function mount(): void
    {
        $batchId = request()->query('batch');
        $this->batchId = is_numeric($batchId) ? (int) $batchId : null;
        $this->refreshBatch();
    }

    public function refreshBatch(): void
    {
        $this->batch = $this->batchId
            ? UnregisteredPsaSendBatch::with(['recipients' => fn ($query) => $query->orderBy('position')])->find($this->batchId)
            : null;
    }

    protected function getViewData(): array
    {
        return [
            'batches' => UnregisteredPsaSendBatch::query()->latest('id')->limit(25)->get(),
        ];
    }

    public function progressPercent(UnregisteredPsaSendBatch $batch): int
    {
        if ($batch->total_count === 0) {
            return 0;
        }

        return min(100, (int) round(($batch->processed_count / $batch->total_count) * 100));
    }
}
