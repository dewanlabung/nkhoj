<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price', 'currency',
        'billing_cycle', 'features', 'stripe_price_id', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function priceFormatted(): string
    {
        if ($this->price === 0) return 'Free';
        return '$' . number_format($this->price / 100, 2) . ' / ' . $this->billing_cycle;
    }

    public function priceInDollars(): float
    {
        return $this->price / 100;
    }
}
