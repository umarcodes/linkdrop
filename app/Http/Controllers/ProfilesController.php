<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Rules\NotReservedUsername;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->profiles()->orderByDesc('is_default')->orderBy('created_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:32', 'alpha_dash', 'unique:profiles,username', new NotReservedUsername],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $profile = $request->user()->profiles()->create([
            ...$validated,
            'is_default' => false,
        ]);

        return response()->json($profile, 201);
    }

    public function update(Request $request, Profile $profile): JsonResponse
    {
        abort_unless($profile->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:500'],
            'theme' => ['nullable', 'array'],
            'theme.accent' => ['nullable', 'string', 'max:20'],
            'theme.bg' => ['nullable', 'string', 'max:20'],
            'theme.card' => ['nullable', 'string', 'max:20'],
            'theme.text' => ['nullable', 'string', 'max:20'],
            'badge_available_for_hire' => ['sometimes', 'boolean'],
            'custom_domain' => ['sometimes', 'nullable', 'string', 'max:255', 'unique:profiles,custom_domain,'.$profile->id],
        ]);

        $profile->update($validated);

        return response()->json($profile->fresh());
    }

    public function destroy(Request $request, Profile $profile): JsonResponse
    {
        abort_unless($profile->user_id === $request->user()->id, 403);
        abort_if($profile->is_default, 422, 'Cannot delete your default profile.');

        $profile->delete();

        return response()->json(['message' => 'Profile deleted.']);
    }

    public function setDefault(Request $request, Profile $profile): JsonResponse
    {
        abort_unless($profile->user_id === $request->user()->id, 403);

        $request->user()->profiles()->update(['is_default' => false]);
        $profile->update(['is_default' => true]);

        return response()->json($profile->fresh());
    }

    public function uploadAvatar(Request $request, Profile $profile): JsonResponse
    {
        abort_unless($profile->user_id === $request->user()->id, 403);

        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpeg,png,gif,webp'],
        ]);

        if ($profile->avatar) {
            $oldPath = str_replace('/storage/', '', parse_url($profile->avatar, PHP_URL_PATH));
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('avatar')->store("avatars/{$request->user()->id}", 'public');
        $profile->update(['avatar' => Storage::disk('public')->url($path)]);

        return response()->json(['avatar' => $profile->avatar]);
    }
}
