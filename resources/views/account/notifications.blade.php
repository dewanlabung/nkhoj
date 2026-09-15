@extends('layouts.account')
@section('title', 'Notification Preferences')

@section('main')
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
    <h1 class="font-bold text-gray-900 dark:text-white mb-1">Notification Preferences</h1>
    <p class="text-sm text-gray-400 mb-6">Choose how you want to be notified for each activity type.</p>

    @if(session('success'))<div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl text-sm">{{ session('success') }}</div>@endif

    <form method="POST" action="/account/notifications">
        @csrf

        {{-- Header --}}
        <div class="hidden sm:grid grid-cols-[1fr_auto_auto_auto] gap-4 text-xs font-semibold text-gray-400 uppercase tracking-wide pb-2 border-b border-gray-100 dark:border-gray-700 mb-1">
            <span>Event</span>
            <span class="w-16 text-center">In-App</span>
            <span class="w-16 text-center">Email</span>
            <span class="w-16 text-center">Push</span>
        </div>

        <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
            @foreach($types as $key => $label)
            @php $pref = $prefs->get($key); @endphp
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_auto_auto] gap-2 sm:gap-4 py-4 items-center">
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $label }}</p>
                </div>
                @foreach(['in_app' => 'In-App', 'email' => 'Email', 'push' => 'Push'] as $channel => $channelLabel)
                <div class="flex sm:justify-center items-center gap-2">
                    <span class="text-xs text-gray-400 sm:hidden">{{ $channelLabel }}:</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="{{ $channel }}_{{ $key }}" value="1"
                            {{ $pref?->{$channel} ? 'checked' : '' }}
                            class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 dark:bg-gray-600 peer-focus:ring-2 peer-focus:ring-brand-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors">
                Save Preferences
            </button>
        </div>
    </form>
</div>
@endsection
