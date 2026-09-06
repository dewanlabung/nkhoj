@extends('layouts.admin')
@section('title', 'Edit — ' . $user->name)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <nav class="text-xs text-gray-400 flex items-center gap-1.5">
        <a href="/admin" class="hover:text-brand-500">Home</a><span>›</span>
        <a href="/admin/users" class="hover:text-brand-500">Users</a><span>›</span>
        <a href="/admin/users/{{ $user->id }}" class="hover:text-brand-500">{{ $user->name }}</a><span>›</span>
        <span>Edit</span>
    </nav>
    <a href="/admin/users/{{ $user->id }}" class="ml-auto flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        View Profile
    </a>
</div>

@if(session('success'))
<div class="mb-5 text-sm text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl px-4 py-3">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-5 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3 space-y-1">
    @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
</div>
@endif

<form method="POST" action="/admin/users/{{ $user->id }}" enctype="multipart/form-data">
    @csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── LEFT: Settings panel ── --}}
    <div class="space-y-5">

        {{-- Avatar --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <div x-data="{ preview: '{{ $user->avatar_url }}' }" class="relative inline-block mb-3">
                <template x-if="preview && preview.length">
                    <img :src="preview" class="w-24 h-24 rounded-full object-cover border-4 border-white dark:border-gray-700 shadow-md mx-auto">
                </template>
                <template x-if="!preview || !preview.length">
                    <div class="w-24 h-24 rounded-full flex items-center justify-center text-3xl font-black text-white shadow-md mx-auto"
                        style="background: hsl({{ crc32($user->name) % 360 }}, 60%, 55%)">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </template>
                <label class="absolute bottom-0 right-0 w-7 h-7 bg-brand-500 hover:bg-brand-600 rounded-full flex items-center justify-center cursor-pointer shadow-md transition-colors">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden"
                        @change="preview = URL.createObjectURL($event.target.files[0])">
                </label>
            </div>
            <p class="text-xs text-gray-400">*.jpg, *.webp, *.png</p>
        </div>

        {{-- Role & status --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Role</label>
                <div class="relative">
                    <select name="role" class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 pr-8 focus:outline-none focus:ring-2 focus:ring-brand-500 appearance-none">
                        @foreach(['reader'=>'Member','reporter'=>'Author','editor'=>'Editor','admin'=>'Super Admin'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $user->role === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-2">Status</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="active" {{ !$user->is_banned ? 'checked' : '' }} class="text-brand-500 focus:ring-brand-400">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="banned" {{ $user->is_banned ? 'checked' : '' }} class="text-red-500 focus:ring-red-400">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Banned</span>
                    </label>
                </div>
            </div>
            <div class="flex items-center justify-between" x-data="{ on: {{ $user->email_verified_at ? 'true' : 'false' }} }">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Email Verified</p>
                    <p class="text-xs text-gray-400">Mark email as verified</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="verify_email" value="1" class="sr-only peer" x-model="on">
                    <div class="w-9 h-5 rounded-full transition-colors peer-checked:bg-green-500 bg-gray-200 dark:bg-gray-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                </label>
            </div>
            <div class="flex items-center justify-between" x-data="{ on: {{ $user->reward_system ? 'true' : 'false' }} }">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Reward System</p>
                    <p class="text-xs text-gray-400">Enable earning rewards</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="reward_system" value="1" class="sr-only peer" x-model="on">
                    <div class="w-9 h-5 rounded-full transition-colors peer-checked:bg-amber-500 bg-gray-200 dark:bg-gray-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                </label>
            </div>
        </div>

        {{-- Balance --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Balance ($)</label>
            <input type="number" step="0.01" min="0" name="balance" value="{{ old('balance', $user->balance) }}"
                class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5 mt-4">Total Page Views</label>
            <input type="number" name="profile_view_count" value="{{ old('profile_view_count', $user->profile_view_count) }}" min="0"
                class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
        </div>

    </div>

    {{-- ── RIGHT: Profile fields ── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Profile Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Profile Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Display Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Username <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg pl-7 pr-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                        placeholder="First Name"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                        placeholder="Last Name"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Bio / About Me</label>
                    <textarea name="bio" rows="3"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none">{{ old('bio', $user->bio) }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Website</label>
                    <input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="https://"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-1">Change Password</h3>
            <p class="text-xs text-gray-400 mb-4">Leave blank to keep current password.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">New Password</label>
                    <input type="password" name="password" minlength="6" placeholder="Min. 6 characters"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 block mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="Repeat password"
                        class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Social Accounts --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">Social Accounts</h3>
            <div class="space-y-3">
                @foreach($platforms as $key => $label)
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-500 dark:text-gray-400 flex-shrink-0 uppercase">{{ substr($key,0,2) }}</span>
                    <div class="flex-1">
                        <label class="text-xs text-gray-400 block mb-0.5">{{ $label }}</label>
                        <input type="url" name="social_links[{{ $key }}]"
                            value="{{ old('social_links.'.$key, $user->getSocialLink($key)) }}"
                            placeholder="https://..."
                            class="w-full text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Save --}}
        <div class="flex justify-end gap-3">
            <a href="/admin/users/{{ $user->id }}"
                class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                Save Changes
            </button>
        </div>

    </div>
</div>
</form>
@endsection
