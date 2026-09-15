<?php

namespace App\Jobs;

use App\Models\UserEngagement\DeviceToken;
use App\Models\Notifications\Notifications\Notification;
use App\Models\Notifications\Notifications\NotificationPreference;
use App\Models\UserEngagement\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly int $userId,
        public readonly string $type,
        public readonly array $data,
    ) {}

    public function handle(): void
    {
        $pref = NotificationPreference::where('user_id', $this->userId)
            ->where('type', $this->type)
            ->first();

        // Default: in_app on, email/push off — matches NotificationPreference::defaultsFor()
        $inApp = $pref ? $pref->in_app : true;
        $email = $pref ? $pref->email  : false;
        $push  = $pref ? $pref->push   : false;

        if ($inApp) {
            Notification::create([
                'user_id' => $this->userId,
                'type'    => $this->type,
                'data'    => $this->data,
            ]);
        }

        if ($email) {
            $this->sendEmail();
        }

        if ($push) {
            $this->sendFcmPush();
        }
    }

    private function sendEmail(): void
    {
        $user = User::find($this->userId);
        if (!$user?->email) {
            return;
        }

        $subject = $this->data['title'] ?? config('app.name') . ' — नयाँ सूचना';
        $body    = $this->data['body']  ?? $this->data['message'] ?? '';
        $url     = $this->data['url']   ?? null;

        try {
            Mail::html(
                view('emails.notification', compact('subject', 'body', 'url', 'user'))->render(),
                function ($m) use ($user, $subject) {
                    $m->to($user->email, $user->name)->subject($subject);
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Notification email failed', ['user' => $this->userId, 'error' => $e->getMessage()]);
        }
    }

    private function sendFcmPush(): void
    {
        $serviceAccountPath = config('services.fcm.service_account_path');
        if (!$serviceAccountPath || !file_exists($serviceAccountPath)) {
            return;
        }

        $tokens = DeviceToken::where('user_id', $this->userId)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $projectId = config('services.fcm.project_id');
        if (!$projectId) {
            return;
        }

        $accessToken = $this->getFcmAccessToken($serviceAccountPath);
        if (!$accessToken) {
            return;
        }

        $title = $this->data['title'] ?? 'नखोज';
        $body  = $this->data['body']  ?? $this->data['message'] ?? '';
        $url   = $this->data['url']   ?? null;

        // FCM HTTP v1 sends one message at a time (no batch registration_ids)
        foreach ($tokens as $token) {
            try {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'webpush' => [
                            'notification' => [
                                'icon'  => '/images/icon-192.png',
                                'click_action' => $url,
                            ],
                            'fcm_options' => array_filter(['link' => $url]),
                        ],
                        'data' => array_filter([
                            'type' => $this->type,
                            'url'  => $url ?? '',
                        ]),
                    ],
                ];

                $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

                if (!$response->successful()) {
                    $errCode = $response->json('error.details.0.errorCode') ?? $response->status();
                    // Remove stale tokens
                    if (in_array($errCode, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                        DeviceToken::where('token', $token)->delete();
                    } else {
                        Log::warning('FCM v1 push failed', ['code' => $errCode, 'user' => $this->userId]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('FCM v1 push exception', ['error' => $e->getMessage(), 'user' => $this->userId]);
            }
        }
    }

    private function getFcmAccessToken(string $serviceAccountPath): ?string
    {
        try {
            $client = new \Google\Client();
            $client->setAuthConfig($serviceAccountPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $token = $client->fetchAccessTokenWithAssertion();
            return $token['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::error('FCM OAuth2 token fetch failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
