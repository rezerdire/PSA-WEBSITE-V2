<?php

namespace App\Jobs;

use App\Services\UnregisteredPsaRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeUnregisteredPsaSendBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(public int $batchId)
    {
    }

    public function handle(UnregisteredPsaRecipientService $service): void
    {
        $service->finalizeBatch($this->batchId);
    }
}
