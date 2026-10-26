<?php

namespace App\Models;

use App\Support\BoothQr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booth extends Model
{
    protected $primaryKey = 'booth_id';

    protected $fillable = [
        'booth_type',
        'booth_name',
        'location',
        'booth_code',
        'booth_generated_code',
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

    /**
     * Booth code + initials of the booth name.
     *   "T1"            + "ALAGAO SCRUBSUIT" -> T1AS
     *   "RB 24, RB 25"  + "GENERAL ELECTRIC" -> RB24-RB25GE
     * If another booth already has that code, -2, -3, ... is appended.
     */
    public static function makeGeneratedCode(string $boothCode, string $boothName, ?int $ignoreId = null): string
    {
        // One booth can cover several codes ("RB 24, RB 25"): strip spaces from each.
        $codes = collect(preg_split('/\s*[,\/&]\s*/', $boothCode, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($c) => strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $c)))
            ->filter()
            ->implode('-');

        $initials = collect(preg_split('/[^A-Za-z0-9]+/', $boothName, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
            ->implode('');

        $base = $codes.$initials;
        $code = $base;
        $i = 2;

        while (static::query()
            ->where('booth_generated_code', $code)
            ->when($ignoreId, fn ($q) => $q->where('booth_id', '!=', $ignoreId))
            ->exists()) {
            $code = $base.'-'.$i++;
        }

        return $code;
    }

    protected static function booted(): void
    {
        // On every create/update: make sure a generated code exists (it can be
        // edited by hand afterwards), then keep the QR in sync with it and
        // remove the previous file if the code changed.
        static::saving(function (Booth $booth) {
            if (blank($booth->booth_generated_code)) {
                $booth->booth_generated_code = static::makeGeneratedCode(
                    (string) $booth->booth_code,
                    (string) $booth->booth_name,
                    $booth->getKey()
                );
            }

            $booth->booth_generated_code = strtoupper(trim($booth->booth_generated_code));

            $old = $booth->getOriginal('booth_qr');
            $new = BoothQr::generate($booth->booth_generated_code);

            if ($old && $old !== $new) {
                BoothQr::delete($old);
            }

            $booth->booth_qr = $new;
        });

        // Booth deleted: remove its QR file too.
        static::deleted(fn (Booth $booth) => BoothQr::delete($booth->booth_qr));
    }
}