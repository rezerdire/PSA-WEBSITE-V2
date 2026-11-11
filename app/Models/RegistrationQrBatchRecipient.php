<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class RegistrationQrBatchRecipient extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_NOT_SELECTED_LIMIT = 'not_selected_limit';

    protected $fillable = [
        'batch_id',
        'registration_id',
        'position',
        'name',
        'email',
        'status',
        'error_message',
        'started_at',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(RegistrationQrBatch::class, 'batch_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
