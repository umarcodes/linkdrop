<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $links = $request->user()->links()->orderBy('order')->get();

        return response()->json($links);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge(['url' => $this->normalizeUrl($request->input('url', ''))]);

        $isHeader = (bool) $request->input('is_header', false);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => $isHeader ? ['nullable', 'string'] : ['required', 'url', 'max:2048', 'regex:#^https?://#i'],
            'icon' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
            'is_header' => ['boolean'],
        ]);

        $user = $request->user();
        $profileId = $user->profile()->value('id');
        $order = ($user->links()->max('order') ?? 0) + 1;

        $link = $user->links()->create([
            ...$validated,
            'profile_id' => $profileId,
            'order' => $order,
            'url' => $isHeader ? null : ($validated['url'] ?? null),
            'is_active' => $validated['is_active'] ?? true,
            'is_header' => $isHeader,
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
            'is_active' => ['sometimes', 'boolean'],
            'is_header' => ['sometimes', 'boolean'],
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
