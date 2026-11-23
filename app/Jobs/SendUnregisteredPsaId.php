<?php

namespace App\Jobs;

use App\Services\UnregisteredPsaRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUnregisteredPsaId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(public int $batchId, public int $batchRecipientId)
    {
    }

    public function handle(UnregisteredPsaRecipientService $service): void
    {
        $service->processQueuedRecipient($this->batchId, $this->batchRecipientId);
    }
}
