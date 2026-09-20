@props(['showTitle' => true, 'showDescription' => true])

<div {{ $attributes->merge(['class' => 'bg-gradient-to-br from-brand-500 to-brand-600 dark:from-brand-600 dark:to-brand-700 rounded-2xl p-6 text-white shadow-lg']) }}>
    @if($showTitle)
    <h3 class="text-lg font-bold mb-2">Newsletter</h3>
    @endif

    @if($showDescription)
    <p class="text-sm text-brand-100 mb-4">Get the latest नेपाली news delivered to your inbox.</p>
    @endif

    <form action="/newsletter/subscribe" method="POST" class="space-y-3">
        @csrf

        <input type="email" name="email" required placeholder="Your email..." maxlength="150"
            class="w-full px-4 py-2.5 rounded-lg text-gray-900 placeholder-gray-600 text-sm focus:ring-2 focus:ring-white focus:outline-none transition"
            value="{{ old('email') }}">

        <button type="submit" class="w-full px-4 py-2.5 bg-white hover:bg-brand-50 text-brand-600 font-semibold rounded-lg transition-colors text-sm">
            Subscribe
        </button>

        @error('email')
        <p class="text-red-200 text-xs">{{ $message }}</p>
        @enderror
    </form>

    <p class="text-xs text-brand-200 mt-3">We respect your privacy. Unsubscribe at any time.</p>
</div>
