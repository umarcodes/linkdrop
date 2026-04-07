<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PaidLinkController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    Route::post('/verify-email', [EmailVerificationController::class, 'verify']);
    Route::post('/waitlist', [WaitlistController::class, 'join']);
});

Route::middleware('throttle:30,1')->get('/domain-lookup', [ProfileController::class, 'domainLookup']);
Route::middleware('throttle:120,1')->get('/p/{username}', [ProfileController::class, 'show']);
Route::middleware('throttle:60,1')->post('/p/{username}/click/{linkId}', [ProfileController::class, 'trackClick']);
Route::middleware('throttle:10,1')->post('/p/{username}/verify/{linkId}', [ProfileController::class, 'verifyLinkPassword']);
Route::middleware('throttle:20,1')->post('/links/{link}/checkout', [PaidLinkController::class, 'checkout']);
Route::middleware('throttle:20,1')->get('/links/{link}/reveal', [PaidLinkController::class, 'reveal']);

// Stripe webhook — raw body required, no CSRF
Route::post('/stripe/webhook', [PaidLinkController::class, 'webhook'])->withoutMiddleware(['web']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/send-verification', [EmailVerificationController::class, 'send']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/account/delete', [AuthController::class, 'deleteAccount']);
    Route::post('/profile/avatar', [AuthController::class, 'uploadAvatar']);

    Route::apiResource('links', LinkController::class);
    Route::post('/links/reorder', [LinkController::class, 'reorder']);
    Route::post('/links/fetch-og', [LinkController::class, 'fetchOg']);
    Route::post('/links/{link}/upload-file', [LinkController::class, 'uploadFile']);

    Route::get('/analytics', [AnalyticsController::class, 'index']);
    Route::get('/analytics/export', [AnalyticsController::class, 'export']);

    // API key management
    Route::post('/api-key/generate', [PublicApiController::class, 'generateKey']);
    Route::post('/api-key/revoke', [PublicApiController::class, 'revokeKey']);

    // Profiles (multiple profiles per account)
    Route::get('/profiles', [ProfilesController::class, 'index']);
    Route::post('/profiles', [ProfilesController::class, 'store']);
    Route::patch('/profiles/{profile}', [ProfilesController::class, 'update']);
    Route::delete('/profiles/{profile}', [ProfilesController::class, 'destroy']);
    Route::post('/profiles/{profile}/set-default', [ProfilesController::class, 'setDefault']);
    Route::post('/profiles/{profile}/avatar', [ProfilesController::class, 'uploadAvatar']);

    // Webhooks
    Route::get('/webhooks', [WebhookController::class, 'index']);
    Route::post('/webhooks', [WebhookController::class, 'store']);
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'stats']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::patch('/users/{user}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);

    Route::get('/waitlist', [WaitlistController::class, 'list']);
    Route::post('/waitlist/invite', [WaitlistController::class, 'invite']);
});

// Public API (API key auth)
Route::middleware('api.key')->prefix('v1')->group(function () {
    Route::get('/links', [PublicApiController::class, 'links']);
    Route::get('/analytics', [PublicApiController::class, 'analytics']);
});
