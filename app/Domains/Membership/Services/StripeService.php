<?php

namespace App\Domains\Membership\Services;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct(private SiteSettingsService $settings) {}

    public function createSession(MembershipPlan $plan, $user): ?array
    {
        $secretKey = $this->settings->stripeKey();
        if (!$secretKey) {
            return null;
        }

        $appUrl = config('app.url', url('/'));
        $body = http_build_query([
            'payment_method_types[]'                          => 'card',
            'line_items[0][price_data][currency]'             => strtolower($plan->currency),
            'line_items[0][price_data][unit_amount]'          => $plan->price,
            'line_items[0][price_data][product_data][name]'   => $plan->name,
            'line_items[0][quantity]'                         => 1,
            'mode'                                            => 'payment',
            'customer_email'                                  => $user->email,
            'metadata[user_id]'                               => $user->id,
            'metadata[plan_id]'                               => $plan->id,
            'success_url'                                     => $appUrl . '/membership/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                                      => $appUrl . '/membership?cancelled=1',
        ]);

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_USERPWD        => $secretKey . ':',
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $session = json_decode($response, true);

        if ($httpCode !== 200 || empty($session['url'])) {
            Log::error('Stripe checkout failed', ['response' => $response]);
            return null;
        }

        return $session;
    }

    public function verifyWebhookSignature(string $payload, string $sigHeader): bool
    {
        $secret = $this->settings->webhookSecret();
        if (!$secret || !$sigHeader) {
            return true; // no secret configured → skip verification
        }

        [$timestamp, $sig] = $this->parseSignature($sigHeader);
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return hash_equals($expected, $sig);
    }

    public function parseSignature(string $header): array
    {
        $timestamp = '';
        $sig = '';
        foreach (explode(',', $header) as $part) {
            [$k, $v] = explode('=', $part, 2);
            if ($k === 't') $timestamp = $v;
            if ($k === 'v1') $sig = $v;
        }
        return [$timestamp, $sig];
    }
}
