<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->webhooks()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'event' => ['sometimes', 'string', 'in:link.clicked,profile.viewed'],
            'secret' => ['nullable', 'string', 'max:255'],
        ]);

        $webhook = $request->user()->webhooks()->create($validated);

        return response()->json($webhook, 201);
    }

    public function destroy(Request $request, Webhook $webhook): JsonResponse
    {
        if ($request->user()->id !== $webhook->user_id) {
            abort(403);
        }

        $webhook->delete();

        return response()->json(['message' => 'Webhook deleted']);
    }
}
