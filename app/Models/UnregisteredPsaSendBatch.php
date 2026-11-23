<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnregisteredPsaSendBatch extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_COMPLETED_WITH_FAILURES = 'completed_with_failures';

    public const STATUS_QUEUE_FAILED = 'queue_failed';

    protected $fillable = [
        'status', 'requested_limit', 'total_count', 'processed_count', 'sent_count', 'failed_count',
        'current_recipient_id', 'current_psa_id', 'current_name', 'current_email', 'current_status',
        'error_message', 'started_at', 'finished_at', 'created_by',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(UnregisteredPsaSendBatchRecipient::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
