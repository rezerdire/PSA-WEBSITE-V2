<?php

namespace App\Models;

use App\Support\BoothQr;
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

    protected static function booted(): void
    {
        // On every create/update: make sure the QR matches the current code,
        // and remove the previous file if the code changed.
        static::saving(function (Booth $booth) {
            $old = $booth->getOriginal('booth_qr');
            $new = BoothQr::generate(strtoupper(trim($booth->booth_code)));

            if ($old && $old !== $new) {
                BoothQr::delete($old);
            }

            $booth->booth_qr = $new;
        });

        // Booth deleted: remove its QR file too.
        static::deleted(fn (Booth $booth) => BoothQr::delete($booth->booth_qr));
    }
}
