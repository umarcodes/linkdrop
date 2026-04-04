<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'url',
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
    ];

    protected $hidden = ['password'];

    protected $appends = ['is_password_protected'];

    public function getIsPasswordProtectedAttribute(): bool
    {
        return ! empty($this->attributes['password']);
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
            'utm_params' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(LinkClick::class);
    }
}
