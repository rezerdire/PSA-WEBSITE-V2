<?php

namespace App\Models;

use App\Mail\RegistrationStatusUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class Registration extends Model
{
    protected $table = 'registrations';

    const STATUS_PENDING  = 'Pending';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';

    protected $fillable = [
        'psa_id', 'prc_number', 'last_name', 'first_name', 'middle_name',
        'hospital_name', 'hospital_address', 'email', 'contact_number', 'membership', 'discount_id', 'proof_payment',
        'status', 'country', 'rejection_title', 'rejection_reason',
    ];

    protected $casts = ['prc_number' => 'integer'];

    protected static function booted(): void
    {
        static::updated(function (Registration $registration) {
            if ($registration->wasChanged('status') && $registration->status !== self::STATUS_PENDING) {
                Mail::to($registration->email)->send(new RegistrationStatusUpdated($registration));
            }
        });
    }

    public function scopePending($query)  { return $query->where('status', self::STATUS_PENDING); }
    public function scopeApproved($query) { return $query->where('status', self::STATUS_APPROVED); }
    public function scopeRejected($query) { return $query->where('status', self::STATUS_REJECTED); }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function isPending(): bool   { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool  { return $this->status === self::STATUS_APPROVED; }
    public function hasDiscount(): bool { return !is_null($this->discount_id); }

    public function member()
    {
        return $this->belongsTo(Member::class, 'psa_id', 'member_id_no');
    }
}