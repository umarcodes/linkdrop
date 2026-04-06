<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendLink(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'If that email exists, a reset link has been sent.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->upsert(
            ['email' => $user->email, 'token' => Hash::make($token), 'created_at' => now()],
            ['email'],
            ['token', 'created_at']
        );

        $resetUrl = config('app.url').'/app/reset-password?token='.$token.'&email='.urlencode($user->email);

        Mail::raw("Reset your password: {$resetUrl}\n\nThis link expires in 60 minutes.", function ($message) use ($user) {
            $message->to($user->email)->subject('Reset your password');
        });

        return response()->json(['message' => 'If that email exists, a reset link has been sent.']);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Invalid or expired reset link.'], 422);
        }

        if (now()->diffInMinutes(Carbon::parse($record->created_at)) > config('linkdrop.token_expiry_minutes')) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json(['message' => 'Invalid or expired reset link.'], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
