<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'avatar',
        'theme',
        'api_key',
        'custom_domain',
        'badge_available_for_hire',
        'badge_verified',
        'is_admin',
        'plan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'theme' => 'array',
            'badge_available_for_hire' => 'boolean',
            'badge_verified' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    public function defaultProfile(): HasOne
    {
        return $this->hasOne(Profile::class)->where('is_default', true);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function profileViews(): HasMany
    {
        return $this->hasMany(ProfileView::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function isPro(): bool
    {
        return in_array($this->plan, ['pro', 'admin'], true) || $this->is_admin;
    }

    public function maxLinks(): int
    {
        return $this->isPro()
            ? config('linkdrop.pro_tier_link_limit')
            : config('linkdrop.free_tier_link_limit');
    }
}
