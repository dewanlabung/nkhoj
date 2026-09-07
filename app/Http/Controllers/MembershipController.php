<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MembershipController extends Controller
{
    private function stripeKey(): ?string
    {
        $path = storage_path('app/site_settings.json');
        $s = File::exists($path) ? (json_decode(File::get($path), true) ?? []) : [];
        return $s['stripe_secret_key'] ?? env('STRIPE_SECRET_KEY');
    }

    private function stripePublicKey(): ?string
    {
        $path = storage_path('app/site_settings.json');
        $s = File::exists($path) ? (json_decode(File::get($path), true) ?? []) : [];
        return $s['stripe_public_key'] ?? env('STRIPE_PUBLIC_KEY');
    }

    private function webhookSecret(): ?string
    {
        $path = storage_path('app/site_settings.json');
        $s = File::exists($path) ? (json_decode(File::get($path), true) ?? []) : [];
        return $s['stripe_webhook_secret'] ?? env('STRIPE_WEBHOOK_SECRET');
    }

    public function plans()
    {
        $plans = MembershipPlan::where('is_active', true)->orderBy('sort_order')->get();
        $activeSub = auth()->check() ? auth()->user()->activeSubscription() : null;

        return view('membership.plans', compact('plans', 'activeSub'));
    }

    private function getSettings(): array
    {
        $path = storage_path('app/site_settings.json');
        return File::exists($path) ? (json_decode(File::get($path), true) ?? []) : [];
    }

    public function checkout(MembershipPlan $plan)
    {
        if (!auth()->check()) {
            return redirect('/login?next=/membership/' . $plan->id . '/checkout');
        }

        $s = $this->getSettings();
        $stripeEnabled = !empty($this->stripeKey());
        $paypalEmail   = $s['paypal_email'] ?? null;
        $paypalMe      = $s['paypal_me'] ?? null;

        $rawBank = [
            'Bank Name'      => $s['bank_name'] ?? null,
            'Account Name'   => $s['bank_account_name'] ?? null,
            'Account Number' => $s['bank_account_number'] ?? null,
            'Routing/SWIFT'  => $s['bank_routing'] ?? null,
        ];
        $bankDetails = array_filter($rawBank) ?: null;

        return view('membership.checkout', compact('plan', 'stripeEnabled', 'paypalEmail', 'paypalMe', 'bankDetails'));
    }

    public function processCheckout(Request $request, MembershipPlan $plan)
    {
        if (!auth()->check()) {
            return redirect('/login?next=/membership/' . $plan->id . '/checkout');
        }

        $method = $request->input('method');
        $user   = auth()->user();

        if ($plan->price === 0 || $method === 'free') {
            $this->activateFreeSubscription($user, $plan);
            return redirect('/membership/success?free=1');
        }

        if ($method === 'stripe') {
            return $this->stripeRedirect($plan, $user);
        }

        if (in_array($method, ['paypal', 'bank'])) {
            Subscription::updateOrCreate(
                ['user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'pending_manual'],
                [
                    'payment_method' => $method,
                    'status'         => 'pending_manual',
                    'starts_at'      => null,
                    'ends_at'        => null,
                ]
            );
            return redirect('/membership/pending')->with('method', $method);
        }

        return redirect('/membership/' . $plan->id . '/checkout')->with('error', 'Please select a payment method.');
    }

    private function stripeRedirect(MembershipPlan $plan, $user)
    {
        $secretKey = $this->stripeKey();
        if (!$secretKey) {
            return redirect('/membership')->with('error', 'Card payment is not configured. Please choose another method.');
        }

        $appUrl = config('app.url', url('/'));
        $body = http_build_query([
            'payment_method_types[]'       => 'card',
            'line_items[0][price_data][currency]'           => strtolower($plan->currency),
            'line_items[0][price_data][unit_amount]'        => $plan->price,
            'line_items[0][price_data][product_data][name]' => $plan->name,
            'line_items[0][quantity]'       => 1,
            'mode'                          => 'payment',
            'customer_email'                => $user->email,
            'metadata[user_id]'             => $user->id,
            'metadata[plan_id]'             => $plan->id,
            'success_url'                   => $appUrl . '/membership/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                    => $appUrl . '/membership?cancelled=1',
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
            return redirect('/membership')->with('error', 'Card payment session could not be created. Please try again or choose another method.');
        }

        Subscription::updateOrCreate(
            ['user_id' => $user->id, 'stripe_session_id' => $session['id']],
            ['plan_id' => $plan->id, 'stripe_session_id' => $session['id'], 'status' => 'pending']
        );

        return redirect($session['url']);
    }

    public function pending()
    {
        return view('membership.pending');
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $isFree    = $request->query('free');

        if ($isFree) {
            return view('membership.success', ['plan' => null, 'isFree' => true]);
        }

        $sub = null;
        if ($sessionId && auth()->check()) {
            $sub = Subscription::where('stripe_session_id', $sessionId)
                ->where('user_id', auth()->id())
                ->with('plan')
                ->first();
        }

        return view('membership.success', ['plan' => $sub?->plan, 'isFree' => false]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = $this->webhookSecret();

        // Verify signature if secret is set
        if ($secret && $sigHeader) {
            [$timestamp, $sig] = $this->parseStripeSignature($sigHeader);
            $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
            if (!hash_equals($expected, $sig)) {
                return response('Invalid signature', 400);
            }
        }

        $event = json_decode($payload, true);
        $type  = $event['type'] ?? '';

        if ($type === 'checkout.session.completed') {
            $session = $event['data']['object'];
            $userId  = $session['metadata']['user_id'] ?? null;
            $planId  = $session['metadata']['plan_id'] ?? null;

            if ($userId && $planId) {
                $plan = MembershipPlan::find($planId);
                if ($plan) {
                    $ends = $this->computeEndDate($plan);
                    Subscription::updateOrCreate(
                        ['stripe_session_id' => $session['id']],
                        [
                            'user_id'                => $userId,
                            'plan_id'                => $planId,
                            'stripe_customer_id'     => $session['customer'] ?? null,
                            'stripe_subscription_id' => $session['subscription'] ?? null,
                            'status'                 => 'active',
                            'starts_at'              => now(),
                            'ends_at'                => $ends,
                        ]
                    );
                }
            }
        }

        return response('OK', 200);
    }

    public function cancel(Request $request)
    {
        $sub = auth()->user()->activeSubscription();
        if (!$sub) {
            return back()->with('error', 'No active subscription to cancel.');
        }

        $sub->update(['status' => 'cancelled']);

        return back()->with('success', 'Subscription cancelled. Access continues until ' . ($sub->ends_at?->format('M d, Y') ?? 'end of period') . '.');
    }

    private function activateFreeSubscription($user, MembershipPlan $plan): void
    {
        Subscription::updateOrCreate(
            ['user_id' => $user->id, 'plan_id' => $plan->id],
            [
                'status'     => 'active',
                'starts_at'  => now(),
                'ends_at'    => null,
            ]
        );
    }

    private function computeEndDate(MembershipPlan $plan): ?\Carbon\Carbon
    {
        return match($plan->billing_cycle) {
            'monthly'  => now()->addMonth(),
            'yearly'   => now()->addYear(),
            'lifetime' => null,
            default    => now()->addMonth(),
        };
    }

    private function parseStripeSignature(string $header): array
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
