<?php

namespace App\Models;

use DateTimeInterface;
use Harryes\SentinelLog\Contracts\NotifiableWithFailedAttempt;
use Harryes\SentinelLog\Contracts\TwoFactorAuthenticatable;
use Harryes\SentinelLog\Traits\NotifiesAuthenticationEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements TwoFactorAuthenticatable, NotifiableWithFailedAttempt
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use NotifiesAuthenticationEvents, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_enabled_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'two_factor_secret'     => 'encrypted',
            'two_factor_enabled_at' => 'datetime',
        ];
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret;
    }

    public function getTwoFactorEnabledAt(): ?DateTimeInterface
    {
        return $this->two_factor_enabled_at;
    }
}
