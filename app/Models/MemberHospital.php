<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberHospital extends Model
{
    protected $table = 'member_hospitals';

    protected $fillable = [
        'member_id_no',
        'hospital',
        'hosp_address',
        'hosp_hours',
        'hosp_tel_no',
        'hosp_designation',
        'hosp_days',
        'hosp_remarks',
        'hosp_primary',
    ];

    protected $casts = [
        'hosp_primary' => 'boolean',
    ];

    /**
     * The member this hospital affiliation belongs to.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id_no', 'member_id_no');
    }
}