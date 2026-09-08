@extends('layouts.admin')
@section('title', 'Deploy')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Deploy</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
        <a href="/admin" class="hover:text-brand-500">Home</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">›</span>
        Deploy
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Manual Deploy --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-white">Manual Deploy</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pull latest code from GitHub and rebuild</p>
            </div>
        </div>

        <div class="text-xs text-gray-500 dark:text-gray-400 mb-4 space-y-1">
            <p>This will run:</p>
            <ol class="list-decimal list-inside space-y-0.5 text-gray-600 dark:text-gray-300">
                <li>git fetch origin master</li>
                <li>git reset --hard origin/master</li>
                <li>composer install (no-dev)</li>
                <li>php artisan migrate --force</li>
                <li>php artisan cache:clear</li>
                <li>php artisan config:clear</li>
                <li>php artisan config:cache</li>
                <li>php artisan view:clear</li>
                <li>php artisan route:cache</li>
            </ol>
        </div>

        <button id="deploy-btn" onclick="runDeploy()"
            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
            <svg id="deploy-icon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <svg id="deploy-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span id="deploy-label">Deploy Now</span>
        </button>

        {{-- Log output --}}
        <div id="deploy-log" class="hidden mt-4 rounded-xl bg-gray-900 text-gray-100 text-xs font-mono p-4 overflow-x-auto max-h-64 overflow-y-auto space-y-3"></div>
    </div>

    {{-- GitHub Webhook --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-white">Auto-Deploy Webhook</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Deploys automatically when you push to GitHub</p>
            </div>
        </div>

        <div class="space-y-4 text-sm text-gray-700 dark:text-gray-300">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Webhook URL</p>
                <div class="flex items-center gap-2">
                    <code class="flex-1 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-xs break-all select-all">{{ url('/webhook/deploy') }}</code>
                    <button onclick="copyWebhook()" class="flex-shrink-0 px-3 py-2 text-xs bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors" title="Copy">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/40 rounded-xl p-3 text-xs text-blue-700 dark:text-blue-300 space-y-1.5">
                <p class="font-semibold">Setup on GitHub:</p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Go to your repo → <strong>Settings → Webhooks → Add webhook</strong></li>
                    <li>Paste the URL above as <strong>Payload URL</strong></li>
                    <li>Set <strong>Content type</strong> to <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">application/json</code></li>
                    <li>Add a <strong>Secret</strong> (optional but recommended) and set <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">DEPLOY_SECRET</code> in your <code>.env</code></li>
                    <li>Choose <strong>Just the push event</strong></li>
                    <li>Click <strong>Add webhook</strong></li>
                </ol>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-3 text-xs space-y-1">
                <p class="font-medium text-gray-600 dark:text-gray-400">Add to your .env file:</p>
                <code class="text-gray-800 dark:text-gray-200">DEPLOY_SECRET=your_secret_here</code>
            </div>
        </div>
    </div>

</div>

<script>
async function runDeploy() {
    const btn   = document.getElementById('deploy-btn');
    const icon  = document.getElementById('deploy-icon');
    const spin  = document.getElementById('deploy-spinner');
    const label = document.getElementById('deploy-label');
    const log   = document.getElementById('deploy-log');

    btn.disabled = true;
    icon.classList.add('hidden');
    spin.classList.remove('hidden');
    label.textContent = 'Deploying…';
    log.innerHTML = '';
    log.classList.remove('hidden');

    try {
        const res = await fetch('/admin/deploy/run', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });
        const data = await res.json();

        data.log.forEach(step => {
            const div = document.createElement('div');
            div.innerHTML = `<div class="flex items-start gap-2">
                <span class="${step.ok ? 'text-green-400' : 'text-red-400'} flex-shrink-0">${step.ok ? '✓' : '✗'}</span>
                <div>
                    <div class="text-yellow-300 font-semibold">$ ${step.cmd}</div>
                    ${step.output ? `<div class="text-gray-300 mt-0.5 whitespace-pre-wrap">${step.output}</div>` : ''}
                </div>
            </div>`;
            log.appendChild(div);
        });

        label.textContent = data.success ? 'Deploy Complete!' : 'Deploy Failed';
        icon.classList.remove('hidden');
        spin.classList.add('hidden');
        btn.disabled = false;

        setTimeout(() => { label.textContent = 'Deploy Now'; }, 4000);
    } catch (e) {
        log.innerHTML = `<div class="text-red-400">Request failed: ${e.message}</div>`;
        label.textContent = 'Deploy Now';
        icon.classList.remove('hidden');
        spin.classList.add('hidden');
        btn.disabled = false;
    }
}

function copyWebhook() {
    const url = '{{ url('/webhook/deploy') }}';
    navigator.clipboard.writeText(url).then(() => {
        const btn = event.currentTarget;
        btn.innerHTML = '<svg class="w-3.5 h-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        setTimeout(() => {
            btn.innerHTML = '<svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';
        }, 2000);
    });
}
</script>
@endsection
