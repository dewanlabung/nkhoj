@extends('layouts.admin')
@section('title', 'Add User')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <nav class="text-xs text-gray-400 flex items-center gap-1.5">
        <a href="/admin" class="hover:text-brand-500">Home</a><span>›</span>
        <a href="/admin/users" class="hover:text-brand-500">Users</a><span>›</span>
        <span>Add User</span>
    </nav>
    <a href="/admin/users" class="ml-auto flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Users
    </a>
</div>

<div class="max-w-xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white text-lg mb-5">Add User</h2>

        @if($errors->any())
        <div class="mb-4 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="/admin/users" class="space-y-4">
            @csrf

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                    placeholder="Full name">
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Username <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                    <input type="text" name="username" value="{{ old('username') }}" required
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg pl-7 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                        placeholder="username">
                </div>
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                    placeholder="user@example.com">
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="6"
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                    placeholder="Min. 6 characters">
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required
                    class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                    placeholder="Repeat password">
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block mb-1.5">Role <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="role"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 pr-8 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all appearance-none">
                        @foreach(['reader'=>'Member','reporter'=>'Author','editor'=>'Editor','admin'=>'Super Admin'] as $val => $label)
                        <option value="{{ $val }}" {{ old('role','reader') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                    + Add User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
