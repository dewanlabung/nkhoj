@auth
<div id="sessionWarningModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" role="dialog">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-8 max-w-sm">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Session Expiring Soon</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Your session will expire in <span id="sessionCountdown">5:00</span> minutes. Click "Stay Logged In" to continue.
            </p>
            <div class="flex gap-3">
                <button id="stayLoggedInBtn" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    Stay Logged In
                </button>
                <button id="logoutBtn" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition">
                    Logout
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionLifetime = {{ config('session.lifetime') }} * 60 * 1000; // Convert to ms
    const warningTime = sessionLifetime - (5 * 60 * 1000); // Show warning 5 minutes before expiry

    let countdownInterval;
    let remainingSeconds = 5 * 60; // 5 minutes

    function updateCountdown() {
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        document.getElementById('sessionCountdown').textContent =
            `${minutes}:${seconds.toString().padStart(2, '0')}`;
        remainingSeconds--;

        if (remainingSeconds < 0) {
            clearInterval(countdownInterval);
            window.location.href = '/logout';
        }
    }

    // Show warning after initial timeout
    setTimeout(() => {
        document.getElementById('sessionWarningModal').classList.remove('hidden');

        // Start countdown
        updateCountdown();
        countdownInterval = setInterval(updateCountdown, 1000);

        // Stay logged in button
        document.getElementById('stayLoggedInBtn').addEventListener('click', () => {
            fetch('/api/session/refresh', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Content-Type': 'application/json'
                }
            })
            .then(() => {
                document.getElementById('sessionWarningModal').classList.add('hidden');
                clearInterval(countdownInterval);
                remainingSeconds = 5 * 60;

                // Reset timer
                setTimeout(() => {
                    document.getElementById('sessionWarningModal').classList.remove('hidden');
                    updateCountdown();
                    countdownInterval = setInterval(updateCountdown, 1000);
                }, sessionLifetime - (5 * 60 * 1000));
            })
            .catch(err => console.error('Session refresh failed:', err));
        });

        // Logout button
        document.getElementById('logoutBtn').addEventListener('click', () => {
            window.location.href = '/logout';
        });
    }, warningTime);

    // Activity tracking (optional: extend session on user activity)
    let activityTimeout;
    function resetActivityTimer() {
        clearTimeout(activityTimeout);
        activityTimeout = setTimeout(() => {
            // Session idle, let warning show
        }, sessionLifetime);
    }

    ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetActivityTimer);
    });

    resetActivityTimer();
});
</script>
@endauth
