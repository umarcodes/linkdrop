<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\NotReservedUsername;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $mode = config('app.registration_mode', 'open');

        if ($mode === 'closed') {
            return response()->json(['message' => 'Registration is currently closed.'], 403);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:32', 'alpha_dash', 'unique:users,username', new NotReservedUsername],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($mode === 'invite') {
            $rules['invite_code'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        if ($mode === 'invite') {
            $entry = DB::table('waitlist')
                ->where('invite_code', $validated['invite_code'])
                ->where('invited', true)
                ->first();

            if (! $entry) {
                return response()->json(['message' => 'Invalid or expired invite code.'], 422);
            }

            DB::table('waitlist')->where('invite_code', $validated['invite_code'])->delete();
        }

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->profiles()->create([
                'username' => $validated['username'],
                'is_default' => true,
            ]);

            return $user;
        });

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $verificationToken = Str::random(64);

        DB::table('email_verification_tokens')->insert([
            'email' => $user->email,
            'token' => hash('sha256', $verificationToken),
            'created_at' => now(),
        ]);

        $url = config('app.url').'/app/verify-email?token='.$verificationToken.'&email='.urlencode($user->email);

        try {
            Mail::raw(
                "Hi {$user->name},\n\nWelcome to LinkDrop! Please verify your email:\n\n{$url}\n\nThis link expires in 60 minutes.",
                function ($m) use ($user) {
                    $m->to($user->email)->subject('Verify your email address');
                }
            );
        } catch (\Throwable) {
            // Non-fatal — user can re-request from dashboard
        }

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json(['user' => $user]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect password.'], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Account deleted.']);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'theme' => ['nullable', 'array'],
            'theme.accent' => ['nullable', 'string', 'max:20'],
            'theme.bg' => ['nullable', 'string', 'max:20'],
            'theme.card' => ['nullable', 'string', 'max:20'],
            'theme.text' => ['nullable', 'string', 'max:20'],
            'badge_available_for_hire' => ['sometimes', 'boolean'],
            'custom_domain' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:users,custom_domain,'.$user->id],
        ]);

        $user->update($validated);

        return response()->json($user->fresh());
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpeg,png,gif,webp'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            $oldPath = str_replace('/storage/', '', parse_url($user->avatar, PHP_URL_PATH));
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');

        $user->update(['avatar' => Storage::disk('public')->url($path)]);

        return response()->json(['avatar' => $user->avatar]);
    }
}
