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
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderByDesc('is_pinned')
            ->orderBy('order')
            ->get(['id', 'title', 'url', 'icon']);

        return response()->json([
            'name' => $user->name,
            'username' => $user->username,
            'bio' => $user->bio,
            'avatar' => $user->avatar,
            'links' => $links,
        ]);
    }

    public function trackClick(Request $request, string $username, int $linkId): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        $link = $user->links()->where('id', $linkId)->where('is_active', true)->firstOrFail();

        $link->clicks()->create([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Click tracked']);
    }
}
