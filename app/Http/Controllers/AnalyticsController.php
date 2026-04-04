<?php

namespace App\Http\Controllers;

use App\Models\LinkClick;
use App\Models\ProfileView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $totalClicks = LinkClick::whereIn('link_id', $linkIds)
            ->where('created_at', '>=', $since)
            ->count();

        $perLink = $user->links()
            ->withCount(['clicks as clicks_count' => fn ($q) => $q->where('created_at', '>=', $since)])
            ->orderBy('order')
            ->get(['id', 'title', 'clicks_count']);

        $dailyClicks = LinkClick::whereIn('link_id', $linkIds)
            ->where('created_at', '>=', $since)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as clicks'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalViews = ProfileView::where('user_id', $user->id)
            ->where('created_at', '>=', $since)
            ->count();

        $clicks = LinkClick::whereIn('link_id', $linkIds)
            ->where('created_at', '>=', $since)
            ->pluck('user_agent');

        $devices = ['Mobile' => 0, 'Desktop' => 0];
        foreach ($clicks as $ua) {
            if (preg_match('/Mobile|Android|iPhone|iPad/i', (string) $ua)) {
                $devices['Mobile']++;
            } else {
                $devices['Desktop']++;
            }
        }

        $referrers = LinkClick::whereIn('link_id', $linkIds)
            ->where('created_at', '>=', $since)
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('COUNT(*) as count'))
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'total_clicks' => $totalClicks,
            'total_views' => $totalViews,
            'per_link' => $perLink,
            'daily_clicks' => $dailyClicks,
            'devices' => $devices,
            'referrers' => $referrers,
            'days' => $days,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $linkIds = $user->links()->pluck('id');

        $clicks = LinkClick::whereIn('link_id', $linkIds)
            ->join('links', 'links.id', '=', 'link_clicks.link_id')
            ->select('links.title', 'links.url', 'link_clicks.ip', 'link_clicks.user_agent', 'link_clicks.referrer', 'link_clicks.created_at')
            ->orderBy('link_clicks.created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($clicks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Link Title', 'URL', 'IP', 'User Agent', 'Referrer', 'Clicked At']);
            foreach ($clicks as $click) {
                fputcsv($handle, [$click->title, $click->url, $click->ip, $click->user_agent, $click->referrer, $click->created_at]);
            }
            fclose($handle);
        }, 'analytics-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
