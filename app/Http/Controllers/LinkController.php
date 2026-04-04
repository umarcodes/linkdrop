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

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048', 'regex:#^https?://#i'],
            'icon' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $order = $request->user()->links()->max('order') + 1;

        $link = $request->user()->links()->create([
            ...$validated,
            'order' => $order,
            'is_active' => $validated['is_active'] ?? true,
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
            'url' => ['sometimes', 'url', 'max:2048', 'regex:#^https?://#i'],
            'icon' => ['nullable', 'string', 'max:10'],
            'is_active' => ['sometimes', 'boolean'],
            'is_pinned' => ['sometimes', 'boolean'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
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
