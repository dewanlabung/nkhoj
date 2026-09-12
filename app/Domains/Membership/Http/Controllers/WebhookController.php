<?php

namespace App\Domains\Membership\Http\Controllers;

use App\Domains\Membership\Services\StripeService;
use App\Domains\Membership\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private StripeService $stripe,
        private SubscriptionService $subscriptions,
    ) {}

    public function stripe(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (!$this->stripe->verifyWebhookSignature($payload, $sigHeader ?? '')) {
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (($event['type'] ?? '') === 'checkout.session.completed') {
            $this->subscriptions->activateFromStripeSession($event['data']['object']);
        }

        return response('OK', 200);
    }
}
