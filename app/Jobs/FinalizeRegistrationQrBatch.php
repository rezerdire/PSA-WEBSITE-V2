<?php

namespace App\Jobs;

use App\Models\RegistrationQrBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeRegistrationQrBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $batchId)
    {
    }

    public function handle(): void
    {
        $batch = RegistrationQrBatch::find($this->batchId);

        if (! $batch || $batch->isFinished()) {
            return;
        }

        $batch->update([
            'status' => $batch->failed_count > 0
                ? RegistrationQrBatch::STATUS_COMPLETED_WITH_FAILURES
                : RegistrationQrBatch::STATUS_COMPLETED,
            'finished_at' => now(),
        ]);
    }
}
