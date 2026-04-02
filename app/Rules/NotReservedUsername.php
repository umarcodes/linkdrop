<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotReservedUsername implements ValidationRule
{
    private const RESERVED = [
        'app', 'api', 'admin', 'superadmin', 'root',
        'login', 'logout', 'register', 'dashboard', 'settings', 'account',
        'profile', 'me', 'user', 'users',
        'help', 'support', 'contact', 'about', 'terms', 'privacy', 'legal',
        'blog', 'news', 'press', 'careers', 'jobs',
        'status', 'health', 'ping', 'up',
        'www', 'mail', 'smtp', 'ftp', 'cdn',
        'linkdrop', 'static', 'assets', 'public', 'storage',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (in_array(strtolower($value), self::RESERVED, strict: true)) {
            $fail('This username is reserved and cannot be used.');
        }
    }
}
