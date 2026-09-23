<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'accounts';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'account_id';

    /**
     * Indicates if the model's primary key is auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The type of the primary key.
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'member_id_no',
        'psa_id',
        'password',
        'is_active',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'reset_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at'      => 'datetime',
        'reset_token_expires_at' => 'datetime',
        'last_login_at'         => 'datetime',
        'is_active'             => 'boolean',
        'must_change_password'  => 'boolean',
        'password'              => 'hashed',
    ];

    /**
     * The member this account belongs to.
     */
    public function member()
    {
        return $this->belongsTo(
            Member::class,
            'member_id_no',
            'member_id_no'
        );
    }

    /**
     * The linked Google identity, if the account has signed in with Google.
     */
    public function googleAccount()
    {
        return $this->hasOne(
            GoogleAccount::class,
            'account_id',
            'account_id'
        );
    }

    /**
     * Whether the account has a password set.
     */
    public function hasPassword(): bool
    {
        return ! is_null($this->password);
    }
}