<?php

namespace App\Jobs;

use App\Models\DeviceToken;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        Notification::create([
            'user_id' => $this->userId,
            'type'    => $this->type,
            'data'    => $this->data,
        ]);

        $this->sendFcmPush();
    }

    private function sendFcmPush(): void
    {
        $serverKey = config('services.fcm.server_key');
        if (!$serverKey) {
            return;
        }

        $tokens = DeviceToken::where('user_id', $this->userId)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $title = $this->data['title'] ?? 'नखोज';
        $body  = $this->data['body']  ?? $this->data['message'] ?? '';
        $url   = $this->data['url']   ?? null;

        foreach (array_chunk($tokens, 500) as $batch) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $batch,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                        'icon'  => '/images/icon-192.png',
                        'click_action' => $url,
                    ],
                    'data' => array_filter([
                        'type' => $this->type,
                        'url'  => $url,
                    ]),
                ]);

                if (!$response->successful()) {
                    Log::warning('FCM push failed', ['status' => $response->status(), 'user' => $this->userId]);
                }
            } catch (\Throwable $e) {
                Log::error('FCM push exception', ['error' => $e->getMessage(), 'user' => $this->userId]);
            }
        }
    }
}
