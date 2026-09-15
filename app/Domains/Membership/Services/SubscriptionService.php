<?php

namespace App\Domains\Membership\Services;

use App\Models\MembershipPlan;
use App\Models\Subscription;

class SubscriptionService
{
    public function activateFree($user, MembershipPlan $plan): void
    {
        Subscription::updateOrCreate(
            ['user_id' => $user->id, 'plan_id' => $plan->id],
            [
                'status'    => 'active',
                'starts_at' => now(),
                'ends_at'   => null,
            ]
        );
    }

    public function activateFromStripeSession(array $session): void
    {
        $userId = $session['metadata']['user_id'] ?? null;
        $planId = $session['metadata']['plan_id'] ?? null;

        if (!$userId || !$planId) {
            return;
        }

        $plan = MembershipPlan::find($planId);
        if (!$plan) {
            return;
        }

        Subscription::updateOrCreate(
            ['stripe_session_id' => $session['id']],
            [
                'user_id'                => $userId,
                'plan_id'                => $planId,
                'stripe_customer_id'     => $session['customer'] ?? null,
                'stripe_subscription_id' => $session['subscription'] ?? null,
                'status'                 => 'active',
                'starts_at'              => now(),
                'ends_at'                => $this->computeEndDate($plan),
            ]
        );
    }

    public function cancel(Subscription $sub): void
    {
        $sub->update(['status' => 'cancelled']);
    }

    public function computeEndDate(MembershipPlan $plan): ?\Carbon\Carbon
    {
        return match ($plan->billing_cycle) {
            'monthly'  => now()->addMonth(),
            'yearly'   => now()->addYear(),
            'lifetime' => null,
            default    => now()->addMonth(),
        };
    }
}
