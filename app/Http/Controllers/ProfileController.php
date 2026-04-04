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
                            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                            ->where(fn ($q) => $q->whereNull('max_clicks')
                                ->orWhereRaw('(SELECT COUNT(*) FROM link_clicks WHERE link_clicks.link_id = links.id) < max_clicks'));
                    });
            })
            ->orderByDesc('is_pinned')
            ->orderBy('order')
            ->get(['id', 'title', 'url', 'icon', 'og_image', 'is_header', 'password']);

        return response()->json([
            'name' => $user->name,
            'username' => $user->username,
            'bio' => $user->bio,
            'avatar' => $user->avatar,
            'theme' => $user->theme,
            'badge_available_for_hire' => $user->badge_available_for_hire,
            'badge_verified' => $user->badge_verified,
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

        $ua = $request->userAgent() ?? '';
        $device = 'desktop';
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            $device = 'tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $ua)) {
            $device = 'mobile';
        }

        $browser = 'Other';
        if (preg_match('/Edg\//i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR\//i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome\//i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\//i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\//i', $ua)) {
            $browser = 'Safari';
        }

        $ip = $request->ip();
        $country = null;

        // Skip private/loopback IPs
        if ($ip && ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $country = null;
        } elseif ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            $ch = curl_init("http://ip-api.com/json/{$ip}?fields=countryCode");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $geo = curl_exec($ch);
            curl_close($ch);
            if ($geo) {
                $data = json_decode($geo, true);
                $country = $data['countryCode'] ?? null;
            }
        }

        $link->clicks()->create([
            'ip' => $ip,
            'user_agent' => $ua,
            'referrer' => $referrer,
            'device' => $device,
            'browser' => $browser,
            'country' => $country,
        ]);

        $webhooks = $user->webhooks()->where('event', 'link.clicked')->where('is_active', true)->get();

        $payload = json_encode([
            'event' => 'link.clicked',
            'link' => ['id' => $link->id, 'title' => $link->title, 'url' => $link->url],
            'ip' => $request->ip(),
            'referrer' => $referrer,
            'at' => now()->toISOString(),
        ]);

        foreach ($webhooks as $webhook) {
            $headers = ['Content-Type: application/json'];
            if ($webhook->secret) {
                $headers[] = 'X-Webhook-Signature: '.hash_hmac('sha256', $payload, $webhook->secret);
            }
            $ch = curl_init($webhook->url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        }

        return response()->json(['message' => 'Click tracked']);
    }
}
