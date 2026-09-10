<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberQr extends Model
{
    protected $table = 'members_qr';

    protected $fillable = [
        'member_id_no',
        'qr_path',
        'qr_hash',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id_no', 'member_id_no');
    }
}