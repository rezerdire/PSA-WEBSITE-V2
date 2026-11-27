<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterList extends Model
{
    protected $fillable = [
        'psa_id',
        'record_type',
        'first_name',
        'last_name',
        'middle_name',
        'payment_ref_no',
        'vip_name',
        'vip_role',
    ];
}
