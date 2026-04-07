<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\LinkPurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaidLinkController extends Controller
{
    /**
     * Create a Stripe Checkout Session for a paid link.
     * Public endpoint — no auth required.
     */
    public function checkout(Request $request, Link $link): JsonResponse
    {
        abort_if(! $link->is_paid, 422, 'This link does not require payment.');

        $profile = $link->profile;
        $successUrl = config('app.url').'/'.($profile ? $profile->username : '').'?paid=1&link='.$link->id.'&session={CHECKOUT_SESSION_ID}';
        $cancelUrl = config('app.url').'/'.($profile ? $profile->username : '');

        $params = http_build_query([
            'line_items[0][price_data][currency]' => $link->currency ?? 'usd',
            'line_items[0][price_data][product_data][name]' => $link->title,
            'line_items[0][price_data][unit_amount]' => $link->price_cents,
            'line_items[0][quantity]' => 1,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata[link_id]' => $link->id,
        ]);

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, config('services.stripe.secret').':');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || ! $response) {
            return response()->json(['message' => 'Failed to create payment session.'], 500);
        }

        $session = json_decode($response, true);

        LinkPurchase::create([
            'link_id' => $link->id,
            'stripe_session_id' => $session['id'],
        ]);

        return response()->json(['checkout_url' => $session['url']]);
    }

    /**
     * Reveal the link URL after a successful payment.
     * Verifies the Stripe session directly (no webhook delay).
     */
    public function reveal(Request $request, Link $link): JsonResponse
    {
        abort_if(! $link->is_paid, 422, 'This link does not require payment.');

        $request->validate(['session_id' => ['required', 'string']]);

        $sessionId = $request->input('session_id');

        // Check if webhook already recorded this purchase
        $purchase = LinkPurchase::where('link_id', $link->id)
            ->where('stripe_session_id', $sessionId)
            ->whereNotNull('paid_at')
            ->first();

        if ($purchase) {
            return response()->json(['url' => $link->url]);
        }

        // Verify directly with Stripe
        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/'.urlencode($sessionId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, config('services.stripe.secret').':');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || ! $response) {
            return response()->json(['message' => 'Could not verify payment.'], 422);
        }

        $session = json_decode($response, true);

        if ($session['payment_status'] !== 'paid') {
            return response()->json(['message' => 'Payment not completed.'], 422);
        }

        if ((int) ($session['metadata']['link_id'] ?? 0) !== $link->id) {
            return response()->json(['message' => 'Session does not match this link.'], 422);
        }

        LinkPurchase::updateOrCreate(
            ['stripe_session_id' => $sessionId],
            [
                'link_id' => $link->id,
                'stripe_payment_intent' => $session['payment_intent'] ?? null,
                'paid_at' => now(),
            ]
        );

        return response()->json(['url' => $link->url]);
    }

    /**
     * Handle Stripe webhook events.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $webhookSecret = config('services.stripe.webhook_secret');

        if ($webhookSecret && $sigHeader) {
            $timestamp = null;
            $signatures = [];

            foreach (explode(',', $sigHeader) as $part) {
                [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
                if ($k === 't') {
                    $timestamp = $v;
                } elseif ($k === 'v1') {
                    $signatures[] = $v;
                }
            }

            $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $webhookSecret);

            if (! in_array($expected, $signatures, true)) {
                return response()->json(['message' => 'Invalid signature.'], 400);
            }
        }

        $event = json_decode($payload, true);

        if (($event['type'] ?? '') === 'checkout.session.completed') {
            $session = $event['data']['object'];

            if ($session['payment_status'] === 'paid' && isset($session['metadata']['link_id'])) {
                LinkPurchase::updateOrCreate(
                    ['stripe_session_id' => $session['id']],
                    [
                        'link_id' => (int) $session['metadata']['link_id'],
                        'stripe_payment_intent' => $session['payment_intent'] ?? null,
                        'paid_at' => now(),
                    ]
                );
            }
        }

        return response()->json(['received' => true]);
    }
}
