@extends('layouts.admin')
@section('title', 'Social Pages')

@section('content')

{{-- Stats row --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    @foreach([
        ['Total Pages',  $stats['total'],    'bg-blue-500',   'text-blue-600 dark:text-blue-400'],
        ['Active',       $stats['active'],   'bg-green-500',  'text-green-600 dark:text-green-400'],
        ['Disabled',     $stats['disabled'], 'bg-red-500',    'text-red-600 dark:text-red-400'],
        ['Verified',     $stats['verified'], 'bg-purple-500', 'text-purple-600 dark:text-purple-400'],
        ['Pending Verif.',$stats['pending'], 'bg-amber-500',  'text-amber-600 dark:text-amber-400'],
    ] as [$label, $val, $color, $accent])
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 shadow-sm">
        <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $val }}</div>
        <div class="text-xs {{ $accent }} mt-0.5 font-medium">{{ $label }}</div>
    </div>
    @endforeach
</div>

@if(session('success'))
<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-400 rounded-xl px-4 py-3 text-sm mb-5">{{ session('success') }}</div>
@endif

{{-- Pending verifications --}}
@if($pendingVerifications->count())
<div class="bg-white dark:bg-gray-800 rounded-xl border border-amber-200 dark:border-amber-700 shadow-sm mb-6 overflow-hidden">
    <div class="px-5 py-3 border-b border-amber-100 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 flex items-center gap-2">
        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        <span class="font-bold text-amber-700 dark:text-amber-400 text-sm">Pending Verification Requests ({{ $pendingVerifications->count() }})</span>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
        @foreach($pendingVerifications as $vr)
        <div class="px-5 py-3 flex items-center gap-4">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ $vr->page->name ?? '—' }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $vr->reason ?? 'No reason given' }} · {{ $vr->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <form method="POST" action="/admin/social-pages/{{ $vr->social_page_id }}/action">
                    @csrf
                    <input type="hidden" name="action" value="approve_verification">
                    <input type="hidden" name="vr_id" value="{{ $vr->id }}">
                    <button class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition">Approve</button>
                </form>
                <form method="POST" action="/admin/social-pages/{{ $vr->social_page_id }}/action">
                    @csrf
                    <input type="hidden" name="action" value="reject_verification">
                    <input type="hidden" name="vr_id" value="{{ $vr->id }}">
                    <button class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition">Reject</button>
                </form>
                <a href="/pages/{{ $vr->page->slug ?? '' }}" target="_blank" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-lg transition">View</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl p-4 shadow-sm">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search pages..."
        class="flex-1 min-w-48 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
    <select name="status" class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
        <option value="">All statuses</option>
        <option value="active" @selected(request('status') === 'active')>Active</option>
        <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
        <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
    </select>
    <select name="verified" class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
        <option value="">Verification</option>
        <option value="yes" @selected(request('verified') === 'yes')>Verified ✓</option>
        <option value="no" @selected(request('verified') === 'no')>Unverified</option>
    </select>
    <select name="category" class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
        <option value="">All categories</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->name }}" @selected(request('category') === $cat->name)>{{ $cat->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">Filter</button>
    <a href="/admin/social-pages" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg transition">Reset</a>
</form>

{{-- Pages table --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 dark:border-gray-700">
                <tr class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    <th class="px-5 py-3">Page</th>
                    <th class="px-4 py-3">Owner</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Followers</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Verified</th>
                    <th class="px-4 py-3">Reports</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                @forelse($pages as $page)
                </tbody><tbody x-data="{ open: false }" class="divide-y divide-gray-50 dark:divide-gray-700/50">
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ $page->avatar }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($page->name) }}&size=32&background=e5e7eb&color=6b7280'">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ Str::limit($page->name, 30) }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $page->page_type }} · {{ $page->created_at->format('M Y') }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                        {{ $page->owner?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                        {{ $page->first_category ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium text-xs">
                        {{ number_format($page->followers_count) }}
                    </td>
                    <td class="px-4 py-3">
                        @php $sc = ['active'=>'green','disabled'=>'red','suspended'=>'amber'][$page->status] ?? 'gray'; @endphp
                        <span class="px-2 py-0.5 bg-{{ $sc }}-100 dark:bg-{{ $sc }}-900/30 text-{{ $sc }}-700 dark:text-{{ $sc }}-400 text-xs font-semibold rounded-full capitalize">{{ $page->status }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($page->is_verified)
                            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @else
                            <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">
                        {{ $page->reports_count > 0 ? $page->reports_count : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="/pages/{{ $page->slug }}" target="_blank"
                               class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 transition" title="View page">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <button @click="open = !open"
                                class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 transition" title="Actions">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                {{-- Expanded actions row --}}
                <tr x-show="open" x-cloak class="bg-gray-50 dark:bg-gray-700/30">
                    <td colspan="8" class="px-5 py-3">
                        <div class="flex flex-wrap gap-2 items-center">
                            {{-- Verify / Unverify --}}
                            @if(!$page->is_verified)
                            <form method="POST" action="/admin/social-pages/{{ $page->id }}/action">
                                @csrf
                                <input type="hidden" name="action" value="verify">
                                <button class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">✓ Verify</button>
                            </form>
                            @else
                            <form method="POST" action="/admin/social-pages/{{ $page->id }}/action" onsubmit="return confirm('Remove verification?')">
                                @csrf
                                <input type="hidden" name="action" value="unverify">
                                <button class="px-3 py-1.5 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-lg transition">Remove Verify</button>
                            </form>
                            @endif

                            {{-- Enable / Disable / Suspend --}}
                            @if($page->status !== 'active')
                            <form method="POST" action="/admin/social-pages/{{ $page->id }}/action">
                                @csrf
                                <input type="hidden" name="action" value="enable">
                                <button class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition">Enable</button>
                            </form>
                            @else
                            <form method="POST" action="/admin/social-pages/{{ $page->id }}/action">
                                @csrf
                                <input type="hidden" name="action" value="disable">
                                <input type="hidden" name="reason" value="Disabled by admin.">
                                <button class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition">Disable</button>
                            </form>
                            <form method="POST" action="/admin/social-pages/{{ $page->id }}/action">
                                @csrf
                                <input type="hidden" name="action" value="suspend">
                                <input type="hidden" name="reason" value="Suspended by admin for policy violation.">
                                <button class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-lg transition">Suspend</button>
                            </form>
                            @endif

                            {{-- Delete --}}
                            <form method="POST" action="/admin/social-pages/{{ $page->id }}/action"
                                  onsubmit="return confirm('Permanently delete this page and all its data?')">
                                @csrf
                                <input type="hidden" name="action" value="delete">
                                <button class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition">Delete</button>
                            </form>

                            <a href="/pages/{{ $page->slug }}/dashboard" target="_blank"
                               class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-lg transition">Dashboard</a>
                        </div>
                    </td>
                </tr>
                @empty
                </tbody><tbody>
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-gray-400 dark:text-gray-500 text-sm">No social pages found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pages->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
        {{ $pages->links() }}
    </div>
    @endif
</div>

@endsection
