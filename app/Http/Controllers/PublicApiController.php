<?php

namespace App\Http\Controllers;

use App\Models\LinkClick;
use App\Models\ProfileView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicApiController extends Controller
{
    public function links(Request $request): JsonResponse
    {
        $links = $request->user()
            ->links()
            ->orderByDesc('is_pinned')
            ->orderBy('order')
            ->get();

        return response()->json($links);
    }

    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $linkIds = $user->links()->pluck('id');

        return response()->json([
            'total_clicks' => LinkClick::whereIn('link_id', $linkIds)->count(),
            'total_views' => ProfileView::where('user_id', $user->id)->count(),
        ]);
    }

    public function generateKey(Request $request): JsonResponse
    {
        $key = Str::random(48);
        $request->user()->update(['api_key' => $key]);

        return response()->json(['api_key' => $key]);
    }

    public function revokeKey(Request $request): JsonResponse
    {
        $request->user()->update(['api_key' => null]);

        return response()->json(['message' => 'API key revoked.']);
    }
}
