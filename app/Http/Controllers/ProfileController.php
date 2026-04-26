<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request, string $username): JsonResponse
    {
        $profile = Profile::with('user')->where('username', $username)->firstOrFail();
        $user = $profile->user;

        $profile->profileViews()->create([
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        $links = $user->links()
            ->where(function ($q) {
                $q->where('is_header', true)
                    ->orWhere('is_active', true);
            })
            ->orderBy('order')
            ->get(['id', 'title', 'url', 'icon', 'type', 'is_header']);

        return response()->json([
            'name' => $user->name,
            'username' => $profile->username,
            'bio' => $profile->bio,
            'avatar' => $profile->avatar,
            'links' => $links,
        ]);
    }

    public function trackClick(Request $request, string $username, int $linkId): JsonResponse
    {
        $profile = Profile::where('username', $username)->firstOrFail();
        $user = $profile->user;

        $link = $user->links()->where('id', $linkId)->where('is_active', true)->where('is_header', false)->firstOrFail();

        $link->clicks()->create([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json(['message' => 'Click tracked']);
    }
}
