<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    Route::post('/verify-email', [EmailVerificationController::class, 'verify']);
});

Route::middleware('throttle:120,1')->get('/p/{username}', [ProfileController::class, 'show']);
Route::middleware('throttle:60,1')->post('/p/{username}/click/{linkId}', [ProfileController::class, 'trackClick']);

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
});
