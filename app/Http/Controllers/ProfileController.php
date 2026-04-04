<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        $user->profileViews()->create(['ip' => $request->ip()]);

        $links = $user->links()
            ->where(function ($q) {
                $q->where('is_header', true)
                    ->orWhere(function ($q) {
                        $q->where('is_active', true)
                            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
                    });
            })
            ->orderByDesc('is_pinned')
            ->orderBy('order')
            ->get(['id', 'title', 'url', 'icon', 'is_header', 'password']);

        return response()->json([
            'name' => $user->name,
            'username' => $user->username,
            'bio' => $user->bio,
            'avatar' => $user->avatar,
            'links' => $links,
        ]);
    }

    public function verifyLinkPassword(Request $request, string $username, int $linkId): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        $link = $user->links()->where('id', $linkId)->where('is_active', true)->where('is_header', false)->firstOrFail();

        if (empty($link->getRawOriginal('password'))) {
            return response()->json(['url' => $link->url]);
        }

        $request->validate(['password' => ['required', 'string']]);

        if ($request->password !== $link->getRawOriginal('password')) {
            return response()->json(['message' => 'Incorrect password.'], 422);
        }

        return response()->json(['url' => $link->url]);
    }

    public function trackClick(Request $request, string $username, int $linkId): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        $link = $user->links()->where('id', $linkId)->where('is_active', true)->where('is_header', false)->firstOrFail();

        $referrer = $request->header('Referer');
        if ($referrer) {
            $parsed = parse_url($referrer);
            $referrer = isset($parsed['host']) ? $parsed['host'] : $referrer;
        }

        $link->clicks()->create([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $referrer,
        ]);

        return response()->json(['message' => 'Click tracked']);
    }
}
