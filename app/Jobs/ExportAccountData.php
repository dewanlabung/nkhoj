<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportAccountData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(public readonly int $userId) {}

    public function handle(): void
    {
        $user = User::with(['posts', 'comments', 'bookmarks', 'questions'])->findOrFail($this->userId);

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name'       => $user->name,
                'username'   => $user->username,
                'email'      => $user->email,
                'bio'        => $user->bio,
                'website'    => $user->website,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'posts'     => $user->posts->map(fn($p) => ['title' => $p->title, 'slug' => $p->slug, 'created_at' => $p->created_at?->toIso8601String()]),
            'questions' => $user->questions->map(fn($q) => ['title' => $q->title, 'created_at' => $q->created_at?->toIso8601String()]),
            'comments'  => $user->comments->map(fn($c) => ['body' => $c->body, 'created_at' => $c->created_at?->toIso8601String()]),
        ];

        $token    = Str::random(64);
        $filename = "exports/user-{$user->id}-{$token}.json";
        Storage::disk('local')->put($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $user->update([
            'data_export_token'    => $token,
            'data_export_ready_at' => now(),
        ]);

        // Send email with download link — silently skip if SMTP is unavailable
        try {
            Mail::raw(
                "Hi {$user->name},\n\nYour account data export is ready.\n\nDownload it here (valid 24 hours):\n" .
                url("/account/export/download?token={$token}") .
                "\n\nThis link expires after 24 hours.\n\n— Nkhoj",
                function ($m) use ($user) {
                    $m->to($user->email)->subject('Your Nkhoj account data export is ready');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ExportAccountData: email send failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
