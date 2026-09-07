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

    {{-- Active subscribers --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
            <h3 class="font-bold text-gray-900 dark:text-white">Active Subscribers</h3>
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
                        <form method="POST" action="/admin/memberships/subscriptions/{{ $sub->id }}/revoke" onsubmit="return confirm('Revoke this subscription?')">
                            @csrf
                            <button class="text-xs text-red-500 hover:text-red-700">Revoke</button>
                        </form>
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
