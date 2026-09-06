{{-- Shared form fields for Add Role modal --}}
<div>
    <label class="text-xs font-bold text-gray-600 dark:text-gray-300 block mb-1.5">Role Name <span class="text-red-400">*</span></label>
    <input type="text" name="name" required placeholder="e.g. Contributor"
        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
    <input type="text" name="name_ne" placeholder="Nepali / other language name"
        class="mt-2 w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
</div>

<div>
    <label class="text-xs font-bold text-gray-600 dark:text-gray-300 block mb-1.5">Slug</label>
    <input type="text" name="slug" placeholder="auto-generated-from-name (lowercase)"
        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 font-mono focus:outline-none focus:ring-2 focus:ring-brand-500">
</div>

<div>
    <label class="text-xs font-bold text-gray-600 dark:text-gray-300 block mb-1.5">User Profile Badge</label>
    <div class="grid grid-cols-2 gap-3">
        <select name="badge_label" class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">— No Badge —</option>
            @foreach($badgeOptions as $b)
            @if($b['value'])
            <option value="{{ $b['value'] }}">{{ $b['label'] }}</option>
            @endif
            @endforeach
        </select>
        <select name="badge_color" class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
            @foreach(['gray'=>'Gray','red'=>'Red','rose'=>'Rose','purple'=>'Purple','green'=>'Green','blue'=>'Blue','indigo'=>'Indigo','teal'=>'Teal','amber'=>'Amber (Gold)'] as $v => $l)
            <option value="{{ $v }}">{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <select name="badge_icon" class="mt-2 w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
        @foreach(['user'=>'User (Default)','shield'=>'Shield','pencil'=>'Pencil / Editor','cog'=>'Cog / Moderator','star'=>'Star / VIP','newspaper'=>'Newspaper / Reporter','plus'=>'Plus / Contributor'] as $v => $l)
        <option value="{{ $v }}">{{ $l }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="text-xs font-bold text-gray-600 dark:text-gray-300 block mb-1">
        AI Credit Tokens
        <span class="ml-1 font-normal text-gray-400">(per month, 0 = disabled, 99999 = unlimited)</span>
    </label>
    <div class="relative">
        <svg class="absolute left-3.5 top-3.5 w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
        <input type="number" name="ai_credits" value="0" min="0" max="99999"
            class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
    </div>
</div>

<div>
    <div class="flex items-center justify-between mb-3">
        <label class="text-xs font-bold text-gray-600 dark:text-gray-300">Permissions</label>
        <div class="flex items-center gap-2">
            <button type="button" onclick="document.querySelectorAll('#addForm input[type=checkbox]').forEach(c=>c.checked=true)" class="text-xs text-brand-500 hover:underline">All</button>
            <span class="text-gray-300">·</span>
            <button type="button" onclick="document.querySelectorAll('#addForm input[type=checkbox]').forEach(c=>c.checked=false)" class="text-xs text-gray-400 hover:text-red-500 hover:underline">Clear</button>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-y-2 gap-x-6 border border-gray-100 dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-gray-700/30 max-h-56 overflow-y-auto">
        @foreach($allPerms as $key => $label)
        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-400 flex-shrink-0">
            <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-brand-500 transition-colors">{{ $label }}</span>
        </label>
        @endforeach
    </div>
</div>
