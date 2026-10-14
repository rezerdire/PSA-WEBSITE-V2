<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booth extends Model
{
  protected $primaryKey = 'booth_id';

    protected $fillable = [
        'booth_type',
        'booth_name',
        'location',
        'booth_code',
        'booth_qr',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(
            BoothAttendance::class,
            'booth_id',
            'booth_id'
        );
    }
}
