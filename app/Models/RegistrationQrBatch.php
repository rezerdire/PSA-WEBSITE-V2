<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class RegistrationQrBatch extends Model
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_COMPLETED_WITH_FAILURES = 'completed_with_failures';

    protected $fillable = [
        'status',
        'requested_limit',
        'total_count',
        'processed_count',
        'sent_count',
        'failed_count',
        'skipped_count',
        'not_selected_count',
        'current_registration_id',
        'current_name',
        'current_email',
        'current_status',
        'error_message',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(RegistrationQrBatchRecipient::class, 'batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentRegistration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'current_registration_id');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_COMPLETED_WITH_FAILURES,
        ], true);
    }
}
