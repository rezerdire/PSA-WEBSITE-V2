<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $table = 'chapters';

    protected $primaryKey = 'psa_chapter_code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'psa_chapter_code',
        'psa_chapter_desc',
        'psa_chapter_address',
        'psa_chapter_president',
        'psa_chapter_contact_no',
    ];

    /**
     * All members belonging to this chapter.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'psa_chapter_code', 'psa_chapter_code');
    }
}