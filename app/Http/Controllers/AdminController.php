<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\LinkClick;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_users' => User::count(),
            'total_links' => Link::count(),
            'total_clicks' => LinkClick::count(),
            'total_views' => ProfileView::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $users = User::withCount(['links', 'profileViews'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($users);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'badge_verified' => ['sometimes', 'boolean'],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function deleteUser(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete yourself.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
