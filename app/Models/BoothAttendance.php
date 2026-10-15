<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BoothAttendance extends Model
{
 protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'member_id_no',
        'booth_id',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(
            Member::class,
            'member_id_no',
            'member_id_no'
        );
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(
            Booth::class,
            'booth_id',
            'booth_id'
        );
    }
}