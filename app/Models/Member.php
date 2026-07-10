<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'members';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'member_id_no';

    /**
     * The "type" of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    protected $fillable = [
        'member_id_no',
        'psa_chapter_code',
        'psa_mem_type',
        'mem_last_name',
        'mem_first_name',
        'mem_middle_name',
        'mem_home_address',
        'mem_mobile_no1',
        'mem_email_address',
        'mem_birth_date',
        'mem_gender',
        'mem_prc_no',
        'mem_pma_id_no',
        'mem_fellow_no',
        'mem_phic_no',
    ];

    protected $casts = [
        'mem_birth_date' => 'date',
    ];

    /**
     * The chapter this member belongs to.
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'psa_chapter_code', 'psa_chapter_code');
    }

    /**
     * The membership type this member holds.
     *
     * NOTE: assumes psa_mem_type stores membership__types.Memtypecode.
     * See the matching note on MembershipType::members().
     */
    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class, 'psa_mem_type', 'Memtypecode');
    }

    /**
     * All hospital affiliations for this member.
     */
    public function hospitals(): HasMany
    {
        return $this->hasMany(MemberHospital::class, 'member_id_no', 'member_id_no');
    }

    /**
     * This member's primary hospital affiliation, if any.
     */
    public function primaryHospital(): HasOne
    {
        return $this->hasOne(MemberHospital::class, 'member_id_no', 'member_id_no')
            ->where('hosp_primary', true);
    }

    /**
     * Convenience accessor for the member's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->mem_first_name} {$this->mem_middle_name} {$this->mem_last_name}");
    }

    // FILAMENT STATS WIDGET
        public function registration(): HasOne
    {
        return $this->hasOne(Registration::class, 'psa_id', 'member_id_no');
    }
}