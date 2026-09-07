@extends('layouts.admin')
@section('title', 'Memberships')

@section('content')
<div class="space-y-6">

    {{-- Stats row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Active Members', 'value' => $stats['active'], 'color' => 'text-green-600', 'bg' => 'bg-green-50 dark:bg-green-900/20'],
            ['label' => 'Total Revenue',  'value' => '$' . number_format($stats['revenue'] / 100, 0), 'color' => 'text-brand-600', 'bg' => 'bg-brand-50 dark:bg-brand-900/20'],
            ['label' => 'This Month',     'value' => $stats['this_month'], 'color' => 'text-blue-600', 'bg' => 'bg-blue-50 dark:bg-blue-900/20'],
            ['label' => 'Plans',          'value' => $stats['plans'], 'color' => 'text-purple-600', 'bg' => 'bg-purple-50 dark:bg-purple-900/20'],
        ] as $s)
        <div class="{{ $s['bg'] }} rounded-xl p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $s['label'] }}</p>
            <p class="text-2xl font-black {{ $s['color'] }}">{{ $s['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Plans management --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm" x-data="{ showForm: false }">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 dark:text-white">Membership Plans</h3>
            <button @click="showForm = !showForm"
                class="text-xs px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Plan
            </button>
        </div>

        {{-- Add plan form --}}
        <div x-show="showForm" x-cloak class="px-5 py-5 bg-gray-50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700">
            <form method="POST" action="/admin/memberships/plans">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Plan Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Pro Monthly"
                            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Price (cents, 0 = free) *</label>
                        <input type="number" name="price" required min="0" placeholder="e.g. 999 = $9.99"
                            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Billing Cycle</label>
                        <select name="billing_cycle"
                            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="lifetime">Lifetime</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Stripe Price ID (optional)</label>
                        <input type="text" name="stripe_price_id" placeholder="price_xxx"
                            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Description</label>
                        <input type="text" name="description" placeholder="Short plan description"
                            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Features (one per line)</label>
                        <textarea name="features_text" rows="4" placeholder="Unlimited Pro articles&#10;Access to recipes&#10;Priority support"
                            class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">Create Plan</button>
                    <button type="button" @click="showForm = false" class="px-5 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-200">Cancel</button>
                </div>
            </form>
        </div>

        {{-- Plans list --}}
        <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
            @forelse($plans as $plan)
            <div class="px-5 py-4 flex items-center gap-4 flex-wrap" x-data="{ editing: false }">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $plan->name }}</p>
                        <span class="text-xs bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 px-2 py-0.5 rounded-full font-semibold">
                            {{ $plan->priceFormatted() }}
                        </span>
                        @if(!$plan->is_active)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        {{ $plan->subscriptions()->where('status', 'active')->count() }} active subscribers
                        @if($plan->stripe_price_id) · Stripe: {{ $plan->stripe_price_id }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button @click="editing = !editing" class="text-xs px-3 py-1.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Edit</button>
                    <form method="POST" action="/admin/memberships/plans/{{ $plan->id }}/toggle">
                        @csrf
                        <button class="text-xs px-3 py-1.5 border {{ $plan->is_active ? 'border-yellow-200 text-yellow-700' : 'border-green-200 text-green-700' }} rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            {{ $plan->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    <form method="POST" action="/admin/memberships/plans/{{ $plan->id }}" onsubmit="return confirm('Delete plan?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-500 hover:text-red-700 px-2">Delete</button>
                    </form>
                </div>

                {{-- Inline edit form --}}
                <div x-show="editing" x-cloak class="w-full mt-2 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                    <form method="POST" action="/admin/memberships/plans/{{ $plan->id }}">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Name</label>
                                <input type="text" name="name" value="{{ $plan->name }}" required
                                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Price (cents)</label>
                                <input type="number" name="price" value="{{ $plan->price }}" min="0" required
                                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Billing Cycle</label>
                                <select name="billing_cycle"
                                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    @foreach(['monthly','yearly','lifetime'] as $c)
                                    <option value="{{ $c }}" {{ $plan->billing_cycle === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Description</label>
                                <input type="text" name="description" value="{{ $plan->description }}"
                                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Stripe Price ID</label>
                                <input type="text" name="stripe_price_id" value="{{ $plan->stripe_price_id }}"
                                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Features (one per line)</label>
                                <textarea name="features_text" rows="3"
                                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none">{{ is_array($plan->features) ? implode("\n", $plan->features) : '' }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-lg">Save Changes</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400 dark:text-gray-500 text-sm">
                No plans yet. Create your first membership plan above.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Stripe settings --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white">Stripe Settings</h3>
            <p class="text-xs text-gray-400 mt-0.5">Configure your Stripe API keys to enable payments.</p>
        </div>
        <div class="px-5 py-5">
            <form method="POST" action="/admin/memberships/stripe-settings" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Stripe Public Key</label>
                    <input type="text" name="stripe_public_key" value="{{ $stripePublicKey }}" placeholder="pk_live_..."
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Stripe Secret Key</label>
                    <input type="password" name="stripe_secret_key" value="{{ $stripeSecretKey }}" placeholder="sk_live_..."
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Webhook Secret</label>
                    <input type="password" name="stripe_webhook_secret" value="{{ $stripeWebhookSecret }}" placeholder="whsec_..."
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    <p class="text-xs text-gray-400 mt-1">Point your Stripe webhook to: <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs">{{ url('/webhook/stripe') }}</code></p>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg">Save Stripe Settings</button>
                </div>
            </form>
        </div>
    </div>

    {{-- PayPal settings --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white">PayPal Settings</h3>
            <p class="text-xs text-gray-400 mt-0.5">Allow members to pay manually via PayPal.</p>
        </div>
        <div class="px-5 py-5">
            <form method="POST" action="/admin/memberships/paypal-settings" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">PayPal Email</label>
                    <input type="email" name="paypal_email" value="{{ $paypalEmail }}" placeholder="you@paypal.com"
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">PayPal.me Link <span class="font-normal text-gray-400">(optional)</span></label>
                    <input type="url" name="paypal_me" value="{{ $paypalMe }}" placeholder="https://paypal.me/yourname"
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">Save PayPal Settings</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bank Transfer settings --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white">Bank Transfer Settings</h3>
            <p class="text-xs text-gray-400 mt-0.5">Allow members to pay via direct bank deposit.</p>
        </div>
        <div class="px-5 py-5">
            <form method="POST" action="/admin/memberships/bank-settings" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ $bankName }}" placeholder="e.g. Nepal Investment Bank"
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Account Name</label>
                    <input type="text" name="bank_account_name" value="{{ $bankAccountName }}" placeholder="e.g. Dewanlabung Media Pvt Ltd"
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Account Number</label>
                    <input type="text" name="bank_account_number" value="{{ $bankAccountNumber }}" placeholder="e.g. 00100012345678"
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Routing / SWIFT / Branch Code</label>
                    <input type="text" name="bank_routing" value="{{ $bankRouting }}" placeholder="e.g. NIBLNPKT or 0010"
                        class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">Save Bank Settings</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Premium Membership Settings (Varient-style) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white">Premium Membership</h3>
            <p class="text-xs text-gray-400 mt-0.5">Control how paywalls and premium content work across your site.</p>
        </div>
        <form method="POST" action="/admin/memberships/premium-settings">
            @csrf
            <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- Left column --}}
                <div class="space-y-6">

                    {{-- Master toggle --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-100 dark:border-gray-600">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">Premium Membership</p>
                            <p class="text-xs text-gray-400 mt-0.5">Turn on to enable subscription plans and paywalls across your website.</p>
                        </div>
                        <label class="relative flex-shrink-0 cursor-pointer" x-data="{ on: {{ $premiumEnabled ? 'true' : 'false' }} }" @click="on = !on">
                            <input type="hidden" name="premium_enabled" value="0">
                            <input type="checkbox" name="premium_enabled" value="1" class="sr-only" :checked="on">
                            <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                            <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                        </label>
                    </div>

                    {{-- Content mode --}}
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Premium Content Mode</p>
                        <div class="space-y-2">
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ $premiumContentMode === 'selected' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/10' : 'border-gray-100 dark:border-gray-600 hover:border-brand-300' }}">
                                <input type="radio" name="premium_content_mode" value="selected" {{ $premiumContentMode === 'selected' ? 'checked' : '' }} class="mt-0.5 accent-brand-500">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Selected Content Only</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Apply premium rules only to specifically selected categories or individual posts.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ $premiumContentMode === 'all' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/10' : 'border-gray-100 dark:border-gray-600 hover:border-brand-300' }}">
                                <input type="radio" name="premium_content_mode" value="all" {{ $premiumContentMode === 'all' ? 'checked' : '' }} class="mt-0.5 accent-brand-500">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">All Website Content</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Force all posts on the website to be premium.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Exclusive / Pay-per-content --}}
                    <div class="border border-gray-100 dark:border-gray-600 rounded-xl p-4 space-y-4">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Exclusive Content (Pay-per-content)</p>
                        <div class="flex items-center justify-between" x-data="{ on: {{ $premiumSingleSales ? 'true' : 'false' }} }">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Single Content Sales</p>
                                <p class="text-xs text-gray-400 mt-0.5">Allows you to sell specific posts individually, even to users who are not subscribed.</p>
                            </div>
                            <label class="relative flex-shrink-0 cursor-pointer ml-4" @click="on = !on">
                                <input type="hidden" name="premium_single_sales" value="0">
                                <input type="checkbox" name="premium_single_sales" value="1" class="sr-only" :checked="on">
                                <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">Default Content Price <span class="text-red-400">*</span></label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-500 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2">$</span>
                                <input type="number" name="premium_default_price" value="{{ $premiumDefaultPrice }}" min="0" step="0.01" placeholder="5"
                                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">This will be the default price when you mark a post as "Exclusive Paid Post". You can override this price on the post creation page.</p>
                        </div>
                    </div>
                </div>

                {{-- Right column --}}
                <div class="space-y-6">

                    {{-- Content hiding method --}}
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Choose Content Hiding Method</p>
                        <div class="space-y-2">
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ $premiumHideMethod === 'hard' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/10' : 'border-gray-100 dark:border-gray-600 hover:border-brand-300' }}">
                                <input type="radio" name="premium_hide_method" value="hard" {{ $premiumHideMethod === 'hard' ? 'checked' : '' }} class="mt-0.5 accent-brand-500">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Hard Paywall</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Hides the content entirely. Only the post title and the paywall box will be displayed.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ $premiumHideMethod !== 'hard' ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/10' : 'border-gray-100 dark:border-gray-600 hover:border-brand-300' }}">
                                <input type="radio" name="premium_hide_method" value="preview" {{ $premiumHideMethod !== 'hard' ? 'checked' : '' }} class="mt-0.5 accent-brand-500">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Premium Preview</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Hides the content after the post title and image.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Subscribe button --}}
                    <div class="border border-gray-100 dark:border-gray-600 rounded-xl p-4 space-y-4">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Subscribe Button</p>
                        <div class="flex items-center justify-between" x-data="{ on: {{ $premiumSubscribeBtnVisible ? 'true' : 'false' }} }">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Button Visibility</p>
                            </div>
                            <label class="relative flex-shrink-0 cursor-pointer ml-4" @click="on = !on">
                                <input type="hidden" name="premium_subscribe_btn_visible" value="0">
                                <input type="checkbox" name="premium_subscribe_btn_visible" value="1" class="sr-only" :checked="on">
                                <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">Color <span class="text-red-400">*</span></label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="premium_subscribe_btn_color" value="{{ $premiumSubscribeBtnColor ?: '#6366f1' }}"
                                    class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer p-1 bg-white dark:bg-gray-700">
                                <input type="text" name="premium_subscribe_btn_color_hex" value="{{ $premiumSubscribeBtnColor ?: '#6366f1' }}" placeholder="#6366f1"
                                    class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                        </div>
                    </div>

                    {{-- Premium badge --}}
                    <div class="border border-gray-100 dark:border-gray-600 rounded-xl p-4 space-y-4">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Premium Badge</p>
                        <div class="flex items-center justify-between" x-data="{ on: {{ $premiumBadgeVisible ? 'true' : 'false' }} }">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Premium Badge Visibility</p>
                                <p class="text-xs text-gray-400 mt-0.5">Show a badge on premium posts in listings and cards.</p>
                            </div>
                            <label class="relative flex-shrink-0 cursor-pointer ml-4" @click="on = !on">
                                <input type="hidden" name="premium_badge_visible" value="0">
                                <input type="checkbox" name="premium_badge_visible" value="1" class="sr-only" :checked="on">
                                <div class="w-11 h-6 rounded-full transition-colors" :class="on ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="on ? 'translate-x-5' : ''"></div>
                            </label>
                        </div>
                        <div x-data="{ label: '{{ $premiumBadgeLabel ?? 'Premium' }}' }">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">Badge Label</label>
                            <input type="text" name="premium_badge_label" x-model="label" placeholder="Premium"
                                class="w-full border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <div class="mt-2">
                                <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full"
                                    :style="'background:' + '{{ $premiumSubscribeBtnColor ?: '#6366f1' }}' + '22; color:' + '{{ $premiumSubscribeBtnColor ?: '#6366f1' }}'">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span x-text="label || 'Premium'"></span>
                                </span>
                                <span class="text-xs text-gray-400 ml-2">Preview</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-50 dark:border-gray-700">
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg">Save Premium Settings</button>
            </div>
        </form>
    </div>

    {{-- Subscribers (active + pending manual) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white">Subscribers</h3>
            <p class="text-xs text-gray-400 mt-0.5">Active + pending manual payments awaiting confirmation</p>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">User</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Plan</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Status</th>
                    <th class="hidden md:table-cell text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Expires</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                @forelse($subscribers as $sub)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300">
                                {{ strtoupper(substr($sub->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $sub->user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $sub->user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300">{{ $sub->plan->name }}</td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            {{ $sub->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell px-5 py-3.5 text-xs text-gray-400">
                        {{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : 'Lifetime' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($sub->status === 'pending_manual')
                            <form method="POST" action="/admin/memberships/subscriptions/{{ $sub->id }}/activate">
                                @csrf
                                <button class="text-xs px-2 py-1 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 font-medium">
                                    ✓ Activate
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="/admin/memberships/subscriptions/{{ $sub->id }}/revoke" onsubmit="return confirm('Revoke this subscription?')">
                                @csrf
                                <button class="text-xs text-red-500 hover:text-red-700">Revoke</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-gray-400 dark:text-gray-500 text-sm">No active subscribers yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($subscribers->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 dark:border-gray-700">{{ $subscribers->links() }}</div>
        @endif
    </div>

</div>
@endsection
