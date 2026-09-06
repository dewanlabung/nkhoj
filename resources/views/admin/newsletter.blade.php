@extends('layouts.admin')
@section('title', 'Newsletter')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Stats + Send form --}}
    <div class="space-y-5">
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                <div class="text-2xl font-black text-gray-900">{{ $totalSubscribers }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Total Subscribers</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
                <div class="text-2xl font-black text-green-600">{{ $activeSubscribers }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Active</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-4">Send Newsletter</h3>
            <form method="POST" action="/admin/newsletter/send" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-600 block mb-1">Subject</label>
                    <input type="text" name="subject" required placeholder="Monthly digest..."
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 block mb-1">Message</label>
                    <textarea name="body" rows="5" required placeholder="Write your newsletter content..."
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>
                <button type="submit" onclick="return confirm('Send to all {{ $activeSubscribers }} active subscribers?')"
                    class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    Send to {{ $activeSubscribers }} subscribers
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 mb-4">Export Subscribers</h3>
            <a href="/admin/newsletter/export" class="block w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors text-center">
                Download CSV
            </a>
        </div>
    </div>

    {{-- Subscribers list --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Subscribers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Email</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Name</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Subscribed</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($subscribers as $sub)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $sub->email }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $sub->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $sub->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            @if($sub->is_active)
                            <span class="text-[10px] font-semibold bg-green-50 text-green-700 px-2 py-1 rounded-full">Active</span>
                            @else
                            <span class="text-[10px] font-semibold bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Unsubscribed</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="/admin/newsletter/{{ $sub->id }}" onsubmit="return confirm('Remove subscriber?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 text-gray-400">
                            <p class="text-3xl mb-2">📧</p>
                            <p>No subscribers yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-50">{{ $subscribers->links() }}</div>
    </div>
</div>
@endsection
