<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    Route::post('/verify-email', [EmailVerificationController::class, 'verify']);
});

Route::get('/p/{username}', [ProfileController::class, 'show']);
Route::post('/p/{username}/click/{linkId}', [ProfileController::class, 'trackClick']);
Route::post('/p/{username}/verify/{linkId}', [ProfileController::class, 'verifyLinkPassword']);

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

    Route::get('/analytics', [AnalyticsController::class, 'index']);
    Route::get('/analytics/export', [AnalyticsController::class, 'export']);

    // API key management
    Route::post('/api-key/generate', [PublicApiController::class, 'generateKey']);
    Route::post('/api-key/revoke', [PublicApiController::class, 'revokeKey']);

    // Webhooks
    Route::get('/webhooks', [WebhookController::class, 'index']);
    Route::post('/webhooks', [WebhookController::class, 'store']);
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy']);
});

// Public API (API key auth)
Route::middleware('api.key')->prefix('v1')->group(function () {
    Route::get('/links', [PublicApiController::class, 'links']);
    Route::get('/analytics', [PublicApiController::class, 'analytics']);
});
