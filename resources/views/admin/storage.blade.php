@extends('layouts.admin')
@section('title', 'Storage')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Storage</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        Storage
    </p>
</div>

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">✓ {{ session('success') }}</div>
@endif

<form method="POST" action="/admin/storage">
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ activeTab: '{{ ($s['active_storage'] ?? 'local') !== 'local' ? ($s['active_storage'] ?? 's3') : 's3' }}' }">

    {{-- Left: Active Storage selector --}}
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-5">Active Storage</h3>

            <div class="space-y-3">
                @php
                    $drivers = [
                        'local' => ['label' => 'Local Storage', 'desc' => 'Store files on the server (default)', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12H3a2 2 0 00-2 2v4a2 2 0 002 2h18a2 2 0 002-2v-4a2 2 0 00-2-2h-2M9 5l3-3 3 3M12 2v13"/>'],
                        's3'    => ['label' => 'Amazon S3',     'desc' => 'AWS Simple Storage Service',          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>'],
                        'r2'    => ['label' => 'Cloudflare R2', 'desc' => 'Zero-egress object storage',          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>'],
                        'b2'    => ['label' => 'Backblaze B2',  'desc' => 'Affordable cloud object storage',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>'],
                    ];
                @endphp
                @foreach($drivers as $key => $driver)
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all
                    {{ ($s['active_storage'] ?? 'local') === $key
                        ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20'
                        : 'border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600' }}">
                    <input type="radio" name="active_storage" value="{{ $key }}"
                        {{ ($s['active_storage'] ?? 'local') === $key ? 'checked' : '' }}
                        @if($key !== 'local') x-on:change="activeTab = '{{ $key }}'" @endif
                        class="w-4 h-4 text-brand-500 border-gray-300 focus:ring-brand-500">
                    <div class="flex items-center gap-2.5 flex-1">
                        <div class="w-8 h-8 rounded-lg {{ ($s['active_storage'] ?? 'local') === $key ? 'bg-brand-100 dark:bg-brand-900/40 text-brand-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-500' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $driver['icon'] !!}</svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $driver['label'] }}</p>
                            <p class="text-[11px] text-gray-400">{{ $driver['desc'] }}</p>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>

            <div class="mt-5 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800/40 rounded-lg text-xs text-yellow-700 dark:text-yellow-400">
                <strong>Note:</strong> Changing active storage does not migrate existing files. Configure the credentials first, then switch.
            </div>
        </div>
    </div>

    {{-- Right: Provider config tabs --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

            {{-- Tab headers --}}
            <div class="flex border-b border-gray-100 dark:border-gray-700">
                @foreach(['s3' => 'AWS S3', 'r2' => 'Cloudflare R2', 'b2' => 'Backblaze B2'] as $tab => $label)
                <button type="button" @click="activeTab = '{{ $tab }}'"
                    :class="activeTab === '{{ $tab }}'
                        ? 'border-b-2 border-brand-500 text-brand-600 dark:text-brand-400'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="px-6 py-3.5 text-sm font-semibold transition-colors">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- AWS S3 --}}
            <div x-show="activeTab === 's3'" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Access Key</label>
                        <input type="text" name="s3[access_key]" value="{{ $s['s3']['access_key'] ?? '' }}"
                            placeholder="AKIAIOSFODNN7EXAMPLE"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Secret Key</label>
                        <input type="password" name="s3[secret_key]" value="{{ $s['s3']['secret_key'] ?? '' }}"
                            placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Bucket Name</label>
                        <input type="text" name="s3[bucket]" value="{{ $s['s3']['bucket'] ?? '' }}"
                            placeholder="my-bucket"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Region</label>
                        <input type="text" name="s3[region]" value="{{ $s['s3']['region'] ?? '' }}"
                            placeholder="us-east-1"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Custom Endpoint URL <span class="font-normal text-gray-400">(optional)</span></label>
                        <input type="text" name="s3[endpoint]" value="{{ $s['s3']['endpoint'] ?? '' }}"
                            placeholder="https://s3.amazonaws.com (leave blank for default)"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Public URL Prefix <span class="font-normal text-gray-400">(optional CDN URL)</span></label>
                        <input type="text" name="s3[url]" value="{{ $s['s3']['url'] ?? '' }}"
                            placeholder="https://cdn.yourdomain.com"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="pt-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 rounded-lg text-xs text-blue-700 dark:text-blue-400">
                    Requires <code class="font-mono bg-blue-100 dark:bg-blue-800/40 px-1 rounded">league/flysystem-aws-s3-v3</code> package. Run <code class="font-mono bg-blue-100 dark:bg-blue-800/40 px-1 rounded">composer require league/flysystem-aws-s3-v3</code> if not installed.
                </div>
            </div>

            {{-- Cloudflare R2 --}}
            <div x-show="activeTab === 'r2'" x-cloak class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Access Key ID</label>
                        <input type="text" name="r2[access_key]" value="{{ $s['r2']['access_key'] ?? '' }}"
                            placeholder="R2 Access Key ID"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Secret Access Key</label>
                        <input type="password" name="r2[secret_key]" value="{{ $s['r2']['secret_key'] ?? '' }}"
                            placeholder="R2 Secret Access Key"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Bucket Name</label>
                        <input type="text" name="r2[bucket]" value="{{ $s['r2']['bucket'] ?? '' }}"
                            placeholder="my-r2-bucket"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Account ID</label>
                        <input type="text" name="r2[account_id]" value="{{ $s['r2']['account_id'] ?? '' }}"
                            placeholder="Cloudflare Account ID"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Public Domain <span class="font-normal text-gray-400">(optional)</span></label>
                        <input type="text" name="r2[public_url]" value="{{ $s['r2']['public_url'] ?? '' }}"
                            placeholder="https://pub-xxx.r2.dev or your custom domain"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="pt-2 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800/40 rounded-lg text-xs text-orange-700 dark:text-orange-400">
                    Cloudflare R2 is S3-compatible. The endpoint is auto-generated from your Account ID as <code class="font-mono bg-orange-100 dark:bg-orange-800/40 px-1 rounded">https://&lt;account_id&gt;.r2.cloudflarestorage.com</code>.
                </div>
            </div>

            {{-- Backblaze B2 --}}
            <div x-show="activeTab === 'b2'" x-cloak class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Key ID (applicationKeyId)</label>
                        <input type="text" name="b2[key_id]" value="{{ $s['b2']['key_id'] ?? '' }}"
                            placeholder="Backblaze Key ID"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Application Key</label>
                        <input type="password" name="b2[app_key]" value="{{ $s['b2']['app_key'] ?? '' }}"
                            placeholder="Backblaze Application Key"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Bucket Name</label>
                        <input type="text" name="b2[bucket]" value="{{ $s['b2']['bucket'] ?? '' }}"
                            placeholder="my-b2-bucket"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">Endpoint Region</label>
                        <input type="text" name="b2[region]" value="{{ $s['b2']['region'] ?? '' }}"
                            placeholder="us-west-004"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1.5">S3-Compatible Endpoint</label>
                        <input type="text" name="b2[endpoint]" value="{{ $s['b2']['endpoint'] ?? '' }}"
                            placeholder="https://s3.us-west-004.backblazeb2.com"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="pt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/40 rounded-lg text-xs text-red-700 dark:text-red-400">
                    Backblaze B2 supports S3-compatible API. Use the S3 endpoint format above. Requires <code class="font-mono bg-red-100 dark:bg-red-800/40 px-1 rounded">league/flysystem-aws-s3-v3</code>.
                </div>
            </div>

        </div>

        <div class="flex justify-end mt-4">
            <button type="submit"
                class="px-8 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                Save Changes
            </button>
        </div>
    </div>
</div>
</form>
@endsection
