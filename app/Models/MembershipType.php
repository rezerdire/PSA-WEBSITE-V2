<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipType extends Model
{
    protected $table = 'membership_types';

    protected $fillable = [
        'Memtypecode',
        'Memtype',
        'stat',
    ];

    protected $casts = [
        'stat' => 'boolean',
    ];

    /**
     * Members holding this membership type.
     *
     * NOTE: soft relationship only — psa_mem_type/Memtypecode are not
     * enforced by a DB-level foreign key (Memtypecode isn't unique).
     * See the matching note on Member::membershipType().
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'psa_mem_type', 'Memtypecode');
    }
}