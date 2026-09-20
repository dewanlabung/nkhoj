@extends('layouts.admin')
@section('title', 'Newsletter')

@section('content')
<div x-data="{ tab: '{{ $tab }}' }" class="space-y-6">
    {{-- Tab Navigation --}}
    <div class="flex gap-2 border-b border-gray-200">
        <button @click="tab = 'subscribers'" :class="tab === 'subscribers' ? 'border-b-2 border-brand-500 text-brand-600' : 'text-gray-600'"
            class="px-4 py-3 font-medium transition-colors">
            Subscribers
        </button>
        <button @click="tab = 'send'" :class="tab === 'send' ? 'border-b-2 border-brand-500 text-brand-600' : 'text-gray-600'"
            class="px-4 py-3 font-medium transition-colors">
            Send Newsletter
        </button>
        <button @click="tab = 'templates'" :class="tab === 'templates' ? 'border-b-2 border-brand-500 text-brand-600' : 'text-gray-600'"
            class="px-4 py-3 font-medium transition-colors">
            Templates
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-2xl font-black text-gray-900">{{ $totalSubscribers }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Total Subscribers</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-2xl font-black text-green-600">{{ $activeSubscribers }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Active</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-2xl font-black text-blue-600">{{ count($templates) }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Templates</div>
        </div>
    </div>

    {{-- Subscribers Tab --}}
    <div x-show="tab === 'subscribers'" class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Subscribers</h2>
            <a href="/admin/newsletter/export" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                📥 Download CSV
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
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
                                <form method="POST" action="/admin/newsletter/{{ $sub->id }}" onsubmit="return confirm('Remove subscriber?')" class="inline">
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

    {{-- Send Newsletter Tab --}}
    <div x-show="tab === 'send'" class="space-y-4">
        <h2 class="text-xl font-bold text-gray-900">Send Newsletter</h2>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <form method="POST" action="/admin/newsletter/send" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-2">Select Template (optional)</label>
                    <select name="template_id" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">— Custom Subject & Body —</option>
                        @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->subject }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-2">Subject</label>
                    <input type="text" name="subject" required placeholder="e.g., Weekly نेपाली News Digest..."
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-2">Message</label>
                    <textarea name="body" rows="8" required placeholder="Write your newsletter content (HTML supported)..."
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none font-mono text-xs"></textarea>
                    <p class="text-xs text-gray-400 mt-2">💡 Tip: Use HTML formatting for better-looking emails</p>
                </div>

                <button type="submit" onclick="return confirm('Send to all {{ $activeSubscribers }} active subscribers?')"
                    class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                    📤 Send to {{ $activeSubscribers }} subscribers
                </button>
            </form>
        </div>
    </div>

    {{-- Templates Tab --}}
    <div x-show="tab === 'templates'" class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Email Templates</h2>
            <button @click="$dispatch('show-modal', 'create-template')" class="px-3 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                ➕ New Template
            </button>
        </div>

        @if($templates->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
            <p class="text-3xl mb-2">📋</p>
            <p class="text-gray-500">No templates yet. Create one to get started.</p>
        </div>
        @else
        <div class="grid gap-4">
            @foreach($templates as $template)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $template->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $template->subject }}</p>
                    </div>
                    @if($template->is_default)
                    <span class="text-[10px] font-semibold bg-blue-50 text-blue-700 px-2 py-1 rounded-full">Default</span>
                    @endif
                </div>
                <div class="flex gap-2">
                    <button @click="$dispatch('edit-template', {{ $template->id }})" class="text-xs px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                        ✏️ Edit
                    </button>
                    <form method="POST" action="/admin/newsletter/templates/{{ $template->id }}" onsubmit="return confirm('Delete this template?')" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-xs px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors">
                            🗑️ Delete
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Create/Edit Template Modal (Simple for now) --}}
<script>
document.addEventListener('show-modal', (e) => {
    if (e.detail === 'create-template') {
        // Simple implementation: just show a form or redirect to a create page
        alert('Create template form coming soon.');
    }
});
</script>
@endsection
