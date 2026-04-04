<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WaitlistController extends Controller
{
    public function join(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        DB::table('waitlist')->updateOrInsert(
            ['email' => $request->email],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return response()->json(['message' => 'You have been added to the waitlist.']);
    }

    public function list(): JsonResponse
    {
        return response()->json(DB::table('waitlist')->orderByDesc('created_at')->get());
    }

    public function invite(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $entry = DB::table('waitlist')->where('email', $request->email)->first();

        if (! $entry) {
            return response()->json(['message' => 'Email not on waitlist.'], 422);
        }

        $code = Str::random(24);

        DB::table('waitlist')->where('email', $request->email)->update([
            'invite_code' => $code,
            'invited' => true,
            'updated_at' => now(),
        ]);

        $url = config('app.url').'/app/register?invite='.$code;

        try {
            Mail::raw(
                "You have been invited to join LinkDrop!\n\nUse this link to create your account:\n\n{$url}\n\nThis invite is single-use.",
                function ($m) use ($request) {
                    $m->to($request->email)->subject("You're invited to LinkDrop!");
                }
            );
        } catch (\Throwable) {
            // Non-fatal
        }

        return response()->json(['message' => 'Invite sent.', 'invite_code' => $code]);
    }
}
