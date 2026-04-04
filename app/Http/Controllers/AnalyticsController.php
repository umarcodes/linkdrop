<?php

namespace App\Http\Controllers;

use App\Models\LinkClick;
use App\Models\ProfileView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $linkIds = $user->links()->pluck('id');

        $totalClicks = LinkClick::whereIn('link_id', $linkIds)->count();

        $perLink = $user->links()
            ->withCount('clicks')
            ->orderBy('order')
            ->get(['id', 'title', 'clicks_count']);

        $dailyClicks = LinkClick::whereIn('link_id', $linkIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as clicks'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalViews = ProfileView::where('user_id', $user->id)->count();

        $clicks = LinkClick::whereIn('link_id', $linkIds)->pluck('user_agent');
        $devices = ['Mobile' => 0, 'Desktop' => 0];
        foreach ($clicks as $ua) {
            if (preg_match('/Mobile|Android|iPhone|iPad/i', (string) $ua)) {
                $devices['Mobile']++;
            } else {
                $devices['Desktop']++;
            }
        }

        return response()->json([
            'total_clicks' => $totalClicks,
            'total_views' => $totalViews,
            'per_link' => $perLink,
            'daily_clicks' => $dailyClicks,
            'devices' => $devices,
        ]);
    }
}
