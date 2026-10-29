<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class WorkshopRegistration extends Model
{
    protected $fillable = [
        'psa_id', 'mem_last_name', 'mem_first_name', 'mem_middle_name', 'mem_type',
        'prc_number', 'hospital', 'email', 'phone', 'workshops',
    ];

   protected function casts(): array
    {
        return [
            'workshops' => 'array',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => collect([
            $this->mem_first_name,
            $this->mem_middle_name,
            $this->mem_last_name,
        ])->filter()->implode(' '));
    }
}
