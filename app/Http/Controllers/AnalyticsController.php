<?php

namespace App\Http\Controllers;

use App\Models\LinkClick;
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

        return response()->json([
            'total_clicks' => $totalClicks,
            'per_link'     => $perLink,
            'daily_clicks' => $dailyClicks,
        ]);
    }
}
