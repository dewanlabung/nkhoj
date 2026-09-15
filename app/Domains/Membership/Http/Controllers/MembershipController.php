<?php

namespace App\Domains\Membership\Http\Controllers;

use App\Domains\Membership\Services\StripeService;
use App\Domains\Membership\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function __construct(
        private SiteSettingsService $settings,
        private StripeService $stripe,
        private SubscriptionService $subscriptions,
    ) {}

    public function plans()
    {
        $plans = MembershipPlan::where('is_active', true)->orderBy('sort_order')->get();
        $activeSub = auth()->check() ? auth()->user()->activeSubscription() : null;

        return view('membership.plans', compact('plans', 'activeSub'));
    }

    public function checkout(MembershipPlan $plan)
    {
        if (!auth()->check()) {
            return redirect('/login?next=/membership/' . $plan->id . '/checkout');
        }

        $s = $this->settings->get();
        $stripeEnabled = !empty($this->settings->stripeKey());
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
            $this->subscriptions->activateFree($user, $plan);
            return redirect('/membership/success?free=1');
        }

        if ($method === 'stripe') {
            $session = $this->stripe->createSession($plan, $user);
            if (!$session) {
                return redirect('/membership')->with('error', 'Card payment is not configured. Please choose another method.');
            }

            Subscription::updateOrCreate(
                ['user_id' => $user->id, 'stripe_session_id' => $session['id']],
                ['plan_id' => $plan->id, 'stripe_session_id' => $session['id'], 'status' => 'pending']
            );

            return redirect($session['url']);
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

    public function cancel(Request $request)
    {
        $sub = auth()->user()->activeSubscription();
        if (!$sub) {
            return back()->with('error', 'No active subscription to cancel.');
        }

        $this->subscriptions->cancel($sub);

        return back()->with('success', 'Subscription cancelled. Access continues until ' . ($sub->ends_at?->format('M d, Y') ?? 'end of period') . '.');
    }
}
