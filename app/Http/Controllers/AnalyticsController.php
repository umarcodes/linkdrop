<?php

namespace App\Http\Controllers;

use App\Models\LinkClick;
use App\Models\ProfileView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'days' => ['sometimes', 'integer', 'min:1', 'max:365'],
        ]);

        $user = $request->user();
        $days = (int) $request->input('days', 7);
        $since = now()->subDays($days);

        $linkIds = $user->links()->pluck('id');

        $clicks = LinkClick::whereIn('link_id', $linkIds)
            ->where('created_at', '>=', $since)
            ->get(['created_at']);

        $totalClicks = $clicks->count();

        $perLink = $user->links()
            ->withCount(['clicks as clicks_count' => fn ($q) => $q->where('created_at', '>=', $since)])
            ->orderBy('order')
            ->get(['id', 'title', 'clicks_count']);

        $totalViews = ProfileView::where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->count();

        $dailyClicks = $clicks
            ->groupBy(fn ($c) => $c->created_at->toDateString())
            ->map(fn ($g, $date) => ['date' => $date, 'clicks' => $g->count()])
            ->sortKeys()
            ->values();

        return response()->json([
            'total_clicks' => $totalClicks,
            'total_views' => $totalViews,
            'per_link' => $perLink,
            'daily_clicks' => $dailyClicks,
            'days' => $days,
        ]);
    }
}
