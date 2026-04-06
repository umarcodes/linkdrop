<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function domainLookup(Request $request): JsonResponse
    {
        $host = $request->query('host') ?: $request->getHost();

        $user = User::where('custom_domain', $host)->first();

        if (! $user) {
            return response()->json(['username' => null], 404);
        }

        return response()->json(['username' => $user->username]);
    }

    public function show(Request $request, string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        $user->profileViews()->create(['ip' => $request->ip()]);

        $links = $user->links()
            ->where(function ($q) {
                $q->where('is_header', true)
                    ->orWhereIn('type', ['tip_jar', 'file'])
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
            ->get(['id', 'title', 'url', 'file_path', 'icon', 'og_image', 'utm_params', 'type', 'is_header', 'password']);

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

        if (! Hash::check($request->password, $link->getRawOriginal('password'))) {
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

        // Only geolocate public, routable IPs; cache results by IP to avoid repeated external calls
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            $country = Cache::remember("geo:{$ip}", now()->addDay(), function () use ($ip) {
                $ch = curl_init("http://ip-api.com/json/{$ip}?fields=countryCode");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $geo = curl_exec($ch);
                curl_close($ch);
                if ($geo) {
                    $data = json_decode($geo, true);

                    return $data['countryCode'] ?? null;
                }

                return null;
            });
        }

        $recorded = DB::transaction(function () use ($link, $ip, $ua, $referrer, $device, $browser, $country) {
            if ($link->max_clicks !== null) {
                $clickCount = DB::table('link_clicks')
                    ->where('link_id', $link->id)
                    ->lockForUpdate()
                    ->count();
                if ($clickCount >= $link->max_clicks) {
                    return false;
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

            return true;
        });

        if (! $recorded) {
            return response()->json(['message' => 'Click limit reached'], 422);
        }

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
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false || $httpCode >= 400) {
                Log::warning('Webhook delivery failed', [
                    'webhook_id' => $webhook->id,
                    'url' => $webhook->url,
                    'http_code' => $httpCode,
                    'curl_error' => $curlError ?: null,
                ]);
            }
        }

        return response()->json(['message' => 'Click tracked']);
    }
}
