<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'google_id', 'email_verified_at'];

    protected $hidden = ['remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'pro_until' => 'datetime',
            'pro_reminder_sent_for' => 'datetime',
            'is_admin' => 'boolean',
            'pro_lifetime' => 'boolean',
        ];
    }

    public function isPro(): bool
    {
        return $this->pro_lifetime || ($this->pro_until !== null && $this->pro_until->isFuture());
    }

    /** Users with Pro right now: lifetime, or a period that has not ended. */
    public function scopePro($query)
    {
        return $query->where(fn ($w) => $w->where('pro_lifetime', true)->orWhere('pro_until', '>', now()));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
