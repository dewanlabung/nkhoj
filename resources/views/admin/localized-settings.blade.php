@extends('layouts.admin')
@section('title', 'Localized Settings')

@push('head')
<style>
.tab-btn { padding: 10px 20px; font-size: 14px; font-weight: 500; border-bottom: 2px solid transparent; color: #6b7280; cursor: pointer; transition: all .15s; white-space: nowrap; }
.tab-btn.active { color: #6366f1; border-bottom-color: #6366f1; }
.tab-btn:hover:not(.active) { color: #374151; }
html.dark .tab-btn { color: #6b7280; }
html.dark .tab-btn.active { color: #818cf8; border-bottom-color: #818cf8; }
html.dark .tab-btn:hover:not(.active) { color: #d1d5db; }
</style>
@endpush

@section('content')
<div class="mb-5">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Localized Settings</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        Localized Settings
    </p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm rounded-xl border border-green-100 dark:border-green-800/40">{{ session('success') }}</div>
@endif

<div x-data="{ tab: '{{ request('tab', 'general') }}', lang: '{{ $lang }}' }">

    {{-- Tab bar + Language selector --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm mb-6">
        <div class="flex items-center justify-between px-6 border-b border-gray-100 dark:border-gray-700 overflow-x-auto">
            <div class="flex -mb-px gap-0">
                @foreach(['general' => 'General', 'contact' => 'Contact Settings', 'social' => 'Social Media', 'cookies' => 'Cookies Warning'] as $key => $label)
                <button @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'active' : ''" class="tab-btn">{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex-shrink-0 ml-4">
                <select x-model="lang" @change="window.location='/admin/localized-settings?lang='+lang+'&tab='+tab"
                    class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none">
                    <option value="en">English</option>
                    <option value="ne">Nepali</option>
                    <option value="ar">Arabic</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ── GENERAL TAB ── --}}
    <div x-show="tab==='general'">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="POST" action="/admin/localized-settings/general" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="lang" :value="lang">

                @foreach([
                    ['app_name',        'Application Name',             'required', $localized['app_name'] ?? 'nkhoj', 'text'],
                    ['date_format',     'Date Format',                  'required', $localized['date_format'] ?? 'M d, Y', 'text'],
                    ['site_title',      'Site Title',                   '',         $localized['site_title'] ?? '', 'text'],
                    ['home_title',      'Home Title',                   '',         $localized['home_title'] ?? 'Index', 'text'],
                    ['site_description','Site Description',              '',         $localized['site_description'] ?? '', 'text'],
                    ['post_url_button', 'Post Optional URL Button Name', '',         $localized['post_url_button'] ?? 'Click Here To See More', 'text'],
                    ['copyright',       'Copyright',                    '',         $localized['copyright'] ?? 'Copyright '.date('Y').' nkhoj - All Rights Reserved.', 'text'],
                ] as [$name, $label, $req, $val, $type])
                <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 sm:text-right">
                        {{ $label }} @if($req)<span class="text-red-400">*</span>@endif
                    </label>
                    <div class="sm:col-span-2">
                        <input type="{{ $type }}" name="{{ $name }}" value="{{ $val }}" {{ $req }}
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                @endforeach

                {{-- Keywords --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 items-start gap-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 sm:text-right pt-2">Keywords</label>
                    <div class="sm:col-span-2">
                        <input type="text" name="keywords" value="{{ $localized['keywords'] ?? '' }}"
                            placeholder="keyword1, keyword2, keyword3"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-400 mt-1">Separate with commas</p>
                    </div>
                </div>

                {{-- Footer About --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 items-start gap-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 sm:text-right pt-2">Footer About Section</label>
                    <div class="sm:col-span-2">
                        <textarea name="footer_about" rows="3"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ $localized['footer_about'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── CONTACT TAB ── --}}
    <div x-show="tab==='contact'" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="POST" action="/admin/localized-settings/contact" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="lang" :value="lang">

                @foreach([
                    ['contact_email',   'Email',   'email', $localized['contact_email'] ?? ''],
                    ['contact_phone',   'Phone',   'text',  $localized['contact_phone'] ?? ''],
                    ['contact_address', 'Address', 'text',  $localized['contact_address'] ?? ''],
                ] as [$name, $label, $type, $val])
                <div class="grid grid-cols-1 sm:grid-cols-4 items-center gap-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                    <div class="sm:col-span-3">
                        <input type="{{ $type }}" name="{{ $name }}" value="{{ $val }}"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                @endforeach

                <div class="grid grid-cols-1 sm:grid-cols-4 items-start gap-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 pt-2">Contact Text</label>
                    <div class="sm:col-span-3">
                        <textarea name="contact_text" rows="5"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ $localized['contact_text'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── SOCIAL MEDIA TAB ── --}}
    <div x-show="tab==='social'" x-cloak>
        <form method="POST" action="/admin/localized-settings/social" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @csrf
            <input type="hidden" name="lang" :value="lang">

            @php
            $platforms = [
                ['twitter',   'X (Twitter)',  '#000000', 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
                ['instagram', 'Instagram',    '#E1306C', 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
                ['facebook',  'Facebook',     '#1877F2', 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
                ['youtube',   'YouTube',      '#FF0000', 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
                ['whatsapp',  'WhatsApp',     '#25D366', 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z'],
                ['linkedin',  'LinkedIn',     '#0A66C2', 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'],
                ['tiktok',    'TikTok',       '#000000', 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z'],
                ['pinterest', 'Pinterest',    '#E60023', 'M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z'],
                ['snapchat',  'Snapchat',     '#FFFC00', 'M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.317 4.Baron M17.739 9.977a1.6 1.6 0 01-.162-.007 3.884 3.884 0 01-.932.095c-.668 0-1.28-.14-1.8-.413-.52-.273-.96-.67-1.246-1.162-.287.493-.727.889-1.246 1.162-.52.274-1.132.413-1.8.413-.302 0-.591-.031-.869-.089-.057.001-.114.003-.172.003l-.162.007c-.744 0-1.354.246-1.641.62-.286.374-.354.869-.178 1.36.176.491.61.885 1.18 1.072.41.134.696.498.696.915 0 .098-.018.192-.05.281-.283.745-.97 1.297-1.8 1.526-.29.08-.438.324-.438.638 0 .315.148.55.377.638.229.089.506.133.786.133.617 0 1.285-.174 1.855-.515C12 18.13 12 18.13 12 18.13s0 0 .001-.001c.569.341 1.238.515 1.855.515.28 0 .557-.044.786-.133.229-.088.377-.323.377-.638 0-.314-.148-.558-.438-.638-.83-.229-1.517-.781-1.8-1.526a.828.828 0 01-.05-.281c0-.417.286-.781.696-.915.57-.187 1.004-.581 1.18-1.072.176-.491.108-.986-.178-1.36-.287-.374-.897-.62-1.641-.62z'],
            ]
            @endphp

            {{-- Website Social Links --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm">Website Social Links</h3>
                    <p class="text-xs text-gray-400">Enter URL to activate. Leave empty to disable.</p>
                </div>
                <div class="space-y-3">
                    @foreach($platforms as [$key, $label, $color, $path])
                    <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background:{{ $color }}">
                            <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24"><path d="{{ $path }}"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</p>
                            <input type="url" name="social_link_{{ $key }}"
                                value="{{ $localized['social_links'][$key] ?? '' }}"
                                placeholder="https://..."
                                class="w-full text-sm bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- User Profile Options --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm">User Profile Options</h3>
                    <p class="text-xs text-gray-400">Select platforms allowed for users.</p>
                </div>
                <div class="space-y-3">
                    @foreach($platforms as [$key, $label, $color, $path])
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/40 rounded-xl p-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background:{{ $color }}">
                                <svg class="w-3.5 h-3.5 fill-white" viewBox="0 0 24 24"><path d="{{ $path }}"/></svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="social_profile_{{ $key }}" value="0">
                            <input type="checkbox" name="social_profile_{{ $key }}" value="1" class="sr-only peer"
                                {{ ($localized['social_profile_options'][$key] ?? true) ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-end mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save Changes</button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── COOKIES WARNING TAB ── --}}
    <div x-show="tab==='cookies'" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <form method="POST" action="/admin/localized-settings/cookies" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="lang" :value="lang">

                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-40">Status</span>
                    <label class="relative inline-flex items-center cursor-pointer gap-2">
                        <input type="hidden" name="cookies_enabled" value="0">
                        <input type="checkbox" name="cookies_enabled" value="1" class="sr-only peer"
                            {{ ($localized['cookies_enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">Enable</span>
                    </label>
                </div>

                @foreach([
                    ['cookies_title',       'Title',       'We Value Your Privacy'],
                    ['cookies_policy_label','Privacy Policy Label', 'Privacy Policy'],
                    ['cookies_policy_url',  'Privacy Policy URL',   ''],
                ] as [$name, $label, $placeholder])
                <div class="grid grid-cols-1 sm:grid-cols-4 items-center gap-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                    <div class="sm:col-span-3">
                        <input type="text" name="{{ $name }}"
                            value="{{ $localized[$name] ?? '' }}"
                            placeholder="{{ $placeholder }}"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                @endforeach

                <div class="grid grid-cols-1 sm:grid-cols-4 items-start gap-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 pt-2">Description</label>
                    <div class="sm:col-span-3">
                        <textarea name="cookies_description" rows="4"
                            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ $localized['cookies_description'] ?? 'We use strictly necessary cookies to ensure our website functions correctly and securely. With your consent, we also use Google Analytics to analyze traffic and improve your user experience.' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
