<?php

namespace App\Http\Controllers;

use App\Models\LinkClick;
use App\Models\ProfileView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        // Single query for all click-level aggregations; avoids 8 separate scans of the same table.
        $clicks = LinkClick::whereIn('link_id', $linkIds)
            ->where('created_at', '>=', $since)
            ->get(['device', 'browser', 'country', 'referrer', 'created_at']);

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

        $devices = $clicks
            ->whereNotNull('device')
            ->groupBy('device')
            ->map->count()
            ->mapWithKeys(fn ($count, $device) => [ucfirst($device) => $count]);

        $browsers = $clicks
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->map->count()
            ->sortByDesc(fn ($v) => $v)
            ->mapWithKeys(fn ($count, $browser) => [$browser => $count]);

        $countries = $clicks
            ->whereNotNull('country')
            ->groupBy('country')
            ->map->count()
            ->sortByDesc(fn ($v) => $v)
            ->take(10)
            ->map(fn ($count, $country) => ['country' => $country, 'count' => $count])
            ->values();

        $referrers = $clicks
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->map->count()
            ->sortByDesc(fn ($v) => $v)
            ->take(10)
            ->map(fn ($count, $referrer) => ['referrer' => $referrer, 'count' => $count])
            ->values();

        $peakHours = $clicks
            ->groupBy(fn ($c) => (int) $c->created_at->format('G'))
            ->map(fn ($g, $hour) => ['hour' => $hour, 'clicks' => $g->count()])
            ->sortBy('hour')
            ->values();

        return response()->json([
            'total_clicks' => $totalClicks,
            'total_views' => $totalViews,
            'per_link' => $perLink,
            'daily_clicks' => $dailyClicks,
            'devices' => $devices,
            'browsers' => $browsers,
            'countries' => $countries,
            'referrers' => $referrers,
            'peak_hours' => $peakHours,
            'days' => $days,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $linkIds = $user->links()->pluck('id');

        $clicks = LinkClick::whereIn('link_id', $linkIds)
            ->join('links', 'links.id', '=', 'link_clicks.link_id')
            ->select('links.title', 'links.url', 'link_clicks.ip', 'link_clicks.user_agent', 'link_clicks.referrer', 'link_clicks.device', 'link_clicks.browser', 'link_clicks.created_at')
            ->orderBy('link_clicks.created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($clicks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Link Title', 'URL', 'IP', 'User Agent', 'Referrer', 'Device', 'Browser', 'Clicked At']);
            foreach ($clicks as $click) {
                fputcsv($handle, [$click->title, $click->url, $click->ip, $click->user_agent, $click->referrer, $click->device, $click->browser, $click->created_at]);
            }
            fclose($handle);
        }, 'analytics-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
