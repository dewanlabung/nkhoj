<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\MembershipPlan;
use App\Models\Subscription;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipAdminController extends BaseAdminController
{
    public function __construct(private SiteSettingsService $settings) {}

    public function memberships()
    {
        $this->requireAdmin();

        $plans       = MembershipPlan::withCount(['subscriptions' => fn($q) => $q->where('status', 'active')])->orderBy('sort_order')->get();
        $subscribers = Subscription::with(['user', 'plan'])
            ->whereIn('status', ['active', 'pending_manual'])
            ->latest()->paginate(20);

        $s = $this->settings->get();
        $stripePublicKey     = $s['stripe_public_key'] ?? '';
        $stripeSecretKey     = $s['stripe_secret_key'] ?? '';
        $stripeWebhookSecret = $s['stripe_webhook_secret'] ?? '';
        $paypalEmail         = $s['paypal_email'] ?? '';
        $paypalMe            = $s['paypal_me'] ?? '';
        $bankName            = $s['bank_name'] ?? '';
        $bankAccountName     = $s['bank_account_name'] ?? '';
        $bankAccountNumber   = $s['bank_account_number'] ?? '';
        $bankRouting         = $s['bank_routing'] ?? '';

        $premiumEnabled             = (bool) ($s['premium_enabled'] ?? false);
        $premiumContentMode         = $s['premium_content_mode'] ?? 'selected';
        $premiumHideMethod          = $s['premium_hide_method'] ?? 'preview';
        $premiumSingleSales         = (bool) ($s['premium_single_sales'] ?? false);
        $premiumDefaultPrice        = $s['premium_default_price'] ?? 5;
        $premiumSubscribeBtnVisible = (bool) ($s['premium_subscribe_btn_visible'] ?? true);
        $premiumSubscribeBtnColor   = $s['premium_subscribe_btn_color'] ?? '#6366f1';
        $premiumBadgeVisible        = (bool) ($s['premium_badge_visible'] ?? true);
        $premiumBadgeLabel          = $s['premium_badge_label'] ?? 'Premium';

        $stats = [
            'active'     => Subscription::where('status', 'active')->count(),
            'revenue'    => Subscription::where('status', 'active')->join('membership_plans', 'subscriptions.plan_id', '=', 'membership_plans.id')->sum('membership_plans.price'),
            'this_month' => Subscription::where('status', 'active')->whereMonth('created_at', now()->month)->count(),
            'plans'      => MembershipPlan::count(),
        ];

        return view('admin.memberships', compact(
            'plans', 'subscribers', 'stats',
            'stripePublicKey', 'stripeSecretKey', 'stripeWebhookSecret',
            'paypalEmail', 'paypalMe',
            'bankName', 'bankAccountName', 'bankAccountNumber', 'bankRouting',
            'premiumEnabled', 'premiumContentMode', 'premiumHideMethod',
            'premiumSingleSales', 'premiumDefaultPrice',
            'premiumSubscribeBtnVisible', 'premiumSubscribeBtnColor',
            'premiumBadgeVisible', 'premiumBadgeLabel'
        ));
    }

    public function storePlan(Request $request)
    {
        $this->requireAdmin();
        $request->validate(['name' => 'required', 'price' => 'required|integer|min:0', 'billing_cycle' => 'in:monthly,yearly,lifetime']);

        $features = array_filter(array_map('trim', explode("\n", $request->input('features_text', ''))));

        MembershipPlan::create([
            'name'            => $request->name,
            'slug'            => Str::slug($request->name),
            'description'     => $request->description,
            'price'           => (int) $request->price,
            'billing_cycle'   => $request->billing_cycle ?? 'monthly',
            'features'        => array_values($features) ?: null,
            'stripe_price_id' => $request->stripe_price_id ?: null,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Plan created.');
    }

    public function updatePlan(Request $request, MembershipPlan $plan)
    {
        $this->requireAdmin();
        $request->validate(['name' => 'required', 'price' => 'required|integer|min:0']);

        $features = array_filter(array_map('trim', explode("\n", $request->input('features_text', ''))));

        $plan->update([
            'name'            => $request->name,
            'description'     => $request->description,
            'price'           => (int) $request->price,
            'billing_cycle'   => $request->billing_cycle,
            'features'        => array_values($features) ?: null,
            'stripe_price_id' => $request->stripe_price_id ?: null,
        ]);

        return back()->with('success', 'Plan updated.');
    }

    public function togglePlan(MembershipPlan $plan)
    {
        $this->requireAdmin();
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', 'Plan updated.');
    }

    public function deletePlan(MembershipPlan $plan)
    {
        $this->requireAdmin();
        $plan->delete();

        return back()->with('success', 'Plan deleted.');
    }

    public function revokeSubscription(Subscription $subscription)
    {
        $this->requireAdmin();
        $subscription->update(['status' => 'cancelled']);

        return back()->with('success', 'Subscription revoked.');
    }

    public function activateSubscription(Subscription $subscription)
    {
        $this->requireAdmin();
        $plan = $subscription->plan;
        $ends = match($plan->billing_cycle ?? 'monthly') {
            'monthly'  => now()->addMonth(),
            'yearly'   => now()->addYear(),
            'lifetime' => null,
            default    => now()->addMonth(),
        };
        $subscription->update([
            'status'    => 'active',
            'starts_at' => now(),
            'ends_at'   => $ends,
        ]);

        return back()->with('success', 'Subscription activated for ' . $subscription->user->name . '.');
    }

    public function updateStripeSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['stripe_public_key']     = $request->input('stripe_public_key', '');
        $s['stripe_secret_key']     = $request->input('stripe_secret_key', '');
        $s['stripe_webhook_secret'] = $request->input('stripe_webhook_secret', '');
        $this->settings->save($s);

        return back()->with('success', 'Stripe settings saved.');
    }

    public function updatePaypalSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['paypal_email'] = $request->input('paypal_email', '');
        $s['paypal_me']    = $request->input('paypal_me', '');
        $this->settings->save($s);

        return back()->with('success', 'PayPal settings saved.');
    }

    public function updateBankSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['bank_name']           = $request->input('bank_name', '');
        $s['bank_account_name']   = $request->input('bank_account_name', '');
        $s['bank_account_number'] = $request->input('bank_account_number', '');
        $s['bank_routing']        = $request->input('bank_routing', '');
        $this->settings->save($s);

        return back()->with('success', 'Bank transfer settings saved.');
    }

    public function updatePremiumSettings(Request $request)
    {
        $this->requireAdmin();
        $s = $this->settings->get();
        $s['premium_enabled']               = $request->boolean('premium_enabled');
        $s['premium_content_mode']          = $request->input('premium_content_mode', 'selected');
        $s['premium_hide_method']           = $request->input('premium_hide_method', 'preview');
        $s['premium_single_sales']          = $request->boolean('premium_single_sales');
        $s['premium_default_price']         = (float) $request->input('premium_default_price', 5);
        $s['premium_subscribe_btn_visible'] = $request->boolean('premium_subscribe_btn_visible');
        $s['premium_subscribe_btn_color']   = $request->input('premium_subscribe_btn_color', '#6366f1');
        $s['premium_badge_visible']         = $request->boolean('premium_badge_visible');
        $s['premium_badge_label']           = $request->input('premium_badge_label', 'Premium');
        $this->settings->save($s);

        return back()->with('success', 'Premium membership settings saved.');
    }
}
