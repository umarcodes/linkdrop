<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $token = Str::random(64);

        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        $url = config('app.url').'/app/verify-email?token='.$token.'&email='.urlencode($user->email);

        Mail::raw(
            "Hi {$user->name},\n\nPlease verify your email by clicking the link below:\n\n{$url}\n\nThis link expires in 60 minutes.",
            function ($m) use ($user) {
                $m->to($user->email)->subject('Verify your email address');
            }
        );

        return response()->json(['message' => 'Verification email sent.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $record = DB::table('email_verification_tokens')->where('email', $request->email)->first();

        if (! $record || ! hash_equals($record->token, hash('sha256', $request->token))) {
            return response()->json(['message' => 'Invalid or expired token.'], 422);
        }

        if (now()->diffInMinutes(Carbon::parse($record->created_at)) > config('linkdrop.token_expiry_minutes')) {
            DB::table('email_verification_tokens')->where('email', $request->email)->delete();

            return response()->json(['message' => 'Token has expired.'], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['email_verified_at' => now()]);

        DB::table('email_verification_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Email verified successfully.']);
    }
}
