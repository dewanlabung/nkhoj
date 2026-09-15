@extends('layouts.app')
@section('title', 'सूचनाहरू — nkhoj')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6 font-nepali flex items-center gap-2">
        🔔 सूचनाहरू
    </h1>

    @if($notifications->count())
    <div class="space-y-3">
        @foreach($notifications as $n)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-start gap-4 {{ $n->read_at ? 'opacity-70' : '' }}">
            <div class="text-2xl flex-shrink-0">
                @if($n->type === 'comment') 💬
                @elseif($n->type === 'follow') 👥
                @elseif($n->type === 'new_post') 📰
                @elseif($n->type === 'reaction') ❤️
                @elseif($n->type === 'bookmark') 🔖
                @elseif($n->type === 'page_admin_invite') 🛡️
                @else 🔔
                @endif
            </div>
            <div class="flex-1 min-w-0">
                @if($n->type === 'comment')
                <p class="text-sm text-gray-800 font-nepali">
                    <strong>{{ $n->data['commenter'] ?? 'कसैले' }}</strong> ले तपाईंको
                    <a href="/posts/{{ $n->data['post_slug'] ?? '#' }}" class="text-brand-600 hover:underline">{{ $n->data['post_title'] ?? 'लेख' }}</a>
                    मा टिप्पणी गर्नुभयो।
                </p>
                @if(!empty($n->data['excerpt']))
                <p class="text-xs text-gray-400 mt-1 italic">"{{ $n->data['excerpt'] }}"</p>
                @endif
                @elseif($n->type === 'follow')
                <p class="text-sm text-gray-800 font-nepali">
                    <a href="/profile/{{ $n->data['username'] ?? '#' }}" class="text-brand-600 hover:underline">{{ $n->data['follower'] ?? 'कसैले' }}</a>
                    ले तपाईंलाई फलो गर्नुभयो।
                </p>
                @elseif($n->type === 'new_post')
                <p class="text-sm text-gray-800 font-nepali">
                    <a href="/profile/{{ $n->data['author_username'] ?? '#' }}" class="text-brand-600 hover:underline">{{ $n->data['author_name'] ?? 'कसैले' }}</a>
                    ले नयाँ लेख प्रकाशन गर्नुभयो:
                    <a href="/posts/{{ $n->data['post_slug'] ?? '#' }}" class="text-brand-600 hover:underline font-medium">{{ $n->data['post_title'] ?? '' }}</a>
                </p>
                @elseif($n->type === 'page_admin_invite')
                <p class="text-sm text-gray-800 font-nepali">
                    <strong>{{ $n->data['inviter'] ?? 'कसैले' }}</strong> ले तपाईंलाई
                    <a href="/pages/{{ $n->data['page_slug'] ?? '#' }}" class="text-brand-600 hover:underline font-semibold">{{ $n->data['page_name'] ?? 'एउटा पेज' }}</a>
                    मा <strong>{{ $n->data['role'] ?? 'admin' }}</strong> को रूपमा आमन्त्रण गर्नुभयो।
                </p>
                @if(empty($n->data['accepted']))
                <form method="POST" action="{{ $n->data['accept_url'] ?? '#' }}" class="mt-2">
                    @csrf
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">
                        Accept Invite
                    </button>
                </form>
                @else
                <p class="text-xs text-green-600 mt-1 font-medium">✓ Accepted</p>
                @endif
                @else
                <p class="text-sm text-gray-800">{{ json_encode($n->data) }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-1.5">{{ $n->created_at->diffForHumans() }}</p>
            </div>
            @if(!$n->read_at)
            <div class="w-2 h-2 bg-brand-500 rounded-full flex-shrink-0 mt-1.5"></div>
            @endif
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $notifications->links() }}</div>
    @else
    <div class="text-center py-20 text-gray-400">
        <div class="text-5xl mb-4">🔔</div>
        <p class="font-nepali">अहिलेसम्म कुनै सूचना छैन।</p>
    </div>
    @endif
</div>
@endsection
