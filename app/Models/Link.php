<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_id',
        'title',
        'url',
        'file_path',
        'icon',
        'og_image',
        'utm_params',
        'type',
        'order',
        'is_active',
        'is_pinned',
        'is_header',
        'starts_at',
        'ends_at',
        'password',
        'max_clicks',
        'price_cents',
        'currency',
    ];

    protected $hidden = ['password'];

    protected $appends = ['is_password_protected', 'is_paid'];

    public function getIsPasswordProtectedAttribute(): bool
    {
        return ! empty($this->attributes['password']);
    }

    public function getIsPaidAttribute(): bool
    {
        return ($this->attributes['price_cents'] ?? 0) > 0;
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null && $value !== '' ? Hash::make($value) : null,
        );
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_pinned' => 'boolean',
            'is_header' => 'boolean',
            'order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'max_clicks' => 'integer',
            'price_cents' => 'integer',
            'utm_params' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(LinkClick::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(LinkPurchase::class);
    }
}
