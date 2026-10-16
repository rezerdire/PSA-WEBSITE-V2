<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopRegistration extends Model
{
    protected $fillable = [
        'psa_id', 'mem_last_name', 'mem_first_name', 'mem_middle_name', 'mem_type',
        'prc_number', 'hospital', 'email', 'phone', 'workshops',
    ];

    protected $casts = ['workshops' => 'array'];
}