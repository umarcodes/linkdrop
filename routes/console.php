<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $expiry = now()->subMinutes(config('linkdrop.token_expiry_minutes'));
    DB::table('email_verification_tokens')->where('created_at', '<', $expiry)->delete();
    DB::table('password_reset_tokens')->where('created_at', '<', $expiry)->delete();
})->hourly()->name('purge-expired-tokens');
