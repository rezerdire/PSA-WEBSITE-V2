<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnregisteredPsaSendBatchRecipient extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'batch_id', 'recipient_id', 'position', 'psa_id', 'name', 'email', 'chapter',
        'status', 'error_message', 'started_at', 'sent_at', 'failed_at',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'sent_at' => 'datetime', 'failed_at' => 'datetime'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(UnregisteredPsaSendBatch::class, 'batch_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(UnregisteredPsaRecipient::class, 'recipient_id');
    }
}
