<?php

namespace App\Jobs;

use App\Models\UserEngagement\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ExportUsersCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $exportPath;

    public function __construct(public int $requesterId)
    {
        $this->exportPath = 'exports/users_' . $requesterId . '_' . now()->format('YmdHis') . '.csv';
    }

    public function handle(): void
    {
        $columns = ['id', 'name', 'username', 'email', 'role', 'is_banned', 'email_verified_at', 'created_at'];

        $csv = implode(',', $columns) . "\n";

        User::select($columns)->orderBy('id')->chunk(200, function ($users) use (&$csv) {
            foreach ($users as $user) {
                $row = array_map(fn($col) => '"' . str_replace('"', '""', (string) ($user->$col ?? '')) . '"', $columns);
                $csv .= implode(',', $row) . "\n";
            }
        });

        Storage::disk('local')->put($this->exportPath, $csv);
    }
}
