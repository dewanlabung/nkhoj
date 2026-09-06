@extends('layouts.admin')
@section('title', 'Themes')

@section('content')

<div class="mb-5">
    <p class="text-sm text-gray-500 dark:text-gray-400">Choose a layout theme for your public website. The active theme controls how your content is displayed to visitors.</p>
</div>

@if(session('success'))
<div class="mb-5 px-4 py-2.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm rounded-xl">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($themes as $key => $theme)
    <div class="bg-white dark:bg-gray-800 rounded-xl border {{ $activeTheme === $key ? 'border-brand-500 ring-2 ring-brand-500/30' : 'border-gray-100 dark:border-gray-700' }} shadow-sm overflow-hidden group transition-all">

        {{-- Theme preview thumbnail --}}
        <div class="relative bg-gray-100 dark:bg-gray-700 overflow-hidden" style="height:160px">
            @include('admin.themes._preview_' . $key)
            @if($activeTheme === $key)
            <div class="absolute top-2 left-2">
                <span class="flex items-center gap-1 px-2.5 py-1 bg-brand-500 text-white text-xs font-bold rounded-full shadow-lg">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Active
                </span>
            </div>
            @endif
        </div>

        {{-- Info + button --}}
        <div class="p-4">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm">{{ $theme['name'] }}</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 mb-3">{{ $theme['description'] }}</p>
            @if($activeTheme === $key)
            <div class="flex items-center gap-1.5 text-xs font-semibold text-brand-600 dark:text-brand-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Currently Active
            </div>
            @else
            <form method="POST" action="/admin/themes/{{ $key }}/activate">
                @csrf
                <button class="w-full py-2 border-2 border-gray-200 dark:border-gray-600 hover:border-brand-500 hover:bg-brand-500 hover:text-white text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-lg transition-all">
                    Activate Theme
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

@endsection
