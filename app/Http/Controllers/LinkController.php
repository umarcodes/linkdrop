<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $links = $request->user()
            ->links()
            ->orderByDesc('is_pinned')
            ->orderBy('order')
            ->get();

        return response()->json($links);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge(['url' => $this->normalizeUrl($request->input('url', ''))]);

        $isHeader = (bool) $request->input('is_header', false);
        $type = $request->input('type', 'link');
        $isTipJar = $type === 'tip_jar';

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ($isHeader || $isTipJar) ? ['nullable', 'string'] : ['required', 'url', 'max:2048', 'regex:#^https?://#i'],
            'icon' => ['nullable', 'string', 'max:10'],
            'type' => ['nullable', 'string', 'in:link,tip_jar'],
            'og_image' => ['nullable', 'url', 'max:500'],
            'utm_params' => ['nullable', 'array'],
            'utm_params.source' => ['nullable', 'string', 'max:100'],
            'utm_params.medium' => ['nullable', 'string', 'max:100'],
            'utm_params.campaign' => ['nullable', 'string', 'max:100'],
            'utm_params.term' => ['nullable', 'string', 'max:100'],
            'utm_params.content' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'is_header' => ['boolean'],
            'password' => ['nullable', 'string', 'max:255'],
            'max_clicks' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $currentCount = $user->links()->count();
        if ($currentCount >= $user->maxLinks()) {
            return response()->json(['message' => "You have reached the link limit for the {$user->plan} plan. Upgrade to Pro for unlimited links."], 422);
        }

        $order = $user->links()->max('order') + 1;

        $link = $user->links()->create([
            ...$validated,
            'order' => $order,
            'url' => ($isHeader || $isTipJar) ? null : ($validated['url'] ?? null),
            'is_active' => $validated['is_active'] ?? true,
            'is_header' => $isHeader,
            'type' => $type,
        ]);

        return response()->json($link, 201);
    }

    public function update(Request $request, Link $link): JsonResponse
    {
        if ($request->user()->id !== $link->user_id) {
            abort(403);
        }

        if ($request->has('url')) {
            $request->merge(['url' => $this->normalizeUrl($request->input('url', ''))]);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'url' => $link->is_header ? ['nullable', 'string'] : ['sometimes', 'url', 'max:2048', 'regex:#^https?://#i'],
            'icon' => ['nullable', 'string', 'max:10'],
            'og_image' => ['sometimes', 'nullable', 'url', 'max:500'],
            'utm_params' => ['sometimes', 'nullable', 'array'],
            'utm_params.source' => ['nullable', 'string', 'max:100'],
            'utm_params.medium' => ['nullable', 'string', 'max:100'],
            'utm_params.campaign' => ['nullable', 'string', 'max:100'],
            'utm_params.term' => ['nullable', 'string', 'max:100'],
            'utm_params.content' => ['nullable', 'string', 'max:100'],
            'type' => ['sometimes', 'nullable', 'string', 'in:link,tip_jar'],
            'is_active' => ['sometimes', 'boolean'],
            'is_pinned' => ['sometimes', 'boolean'],
            'is_header' => ['sometimes', 'boolean'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'password' => ['sometimes', 'nullable', 'string', 'max:255'],
            'max_clicks' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);

        $link->update($validated);

        return response()->json($link);
    }

    public function destroy(Request $request, Link $link): JsonResponse
    {
        if ($request->user()->id !== $link->user_id) {
            abort(403);
        }

        $link->delete();

        return response()->json(['message' => 'Link deleted']);
    }

    private function normalizeUrl(string $url): string
    {
        if ($url !== '' && ! preg_match('#^https?://#i', $url)) {
            return 'https://'.$url;
        }

        return $url;
    }

    public function fetchOg(Request $request): JsonResponse
    {
        $request->validate(['url' => ['required', 'url', 'max:2048']]);

        $url = $request->input('url');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; LinkDrop/1.0)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        curl_close($ch);

        $ogImage = null;
        $ogTitle = null;

        if ($html) {
            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
                $ogImage = $m[1];
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\'][^>]*>/i', $html, $m)) {
                $ogImage = $m[1];
            }

            if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
                $ogTitle = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\'][^>]*>/i', $html, $m)) {
                $ogTitle = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } elseif (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
                $ogTitle = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        return response()->json(['og_image' => $ogImage, 'og_title' => $ogTitle]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'links' => ['required', 'array'],
            'links.*.id' => ['required', 'integer', 'exists:links,id'],
            'links.*.order' => ['required', 'integer', 'min:0'],
        ]);

        $ids = collect($request->links)->pluck('id');

        $ownedCount = $request->user()->links()->whereIn('id', $ids)->count();

        if ($ownedCount !== $ids->count()) {
            abort(403, 'You do not own all of the specified links.');
        }

        foreach ($request->links as $item) {
            $request->user()
                ->links()
                ->where('id', $item['id'])
                ->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Links reordered']);
    }
}
