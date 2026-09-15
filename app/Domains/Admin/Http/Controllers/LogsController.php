<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\OutgoingEmailLog;
use App\Models\ScheduleLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class LogsController extends BaseAdminController
{
    public function index(Request $request)
    {
        $this->requireAdmin();

        $tab = $request->get('tab', 'schedule');

        $scheduleLogs  = ScheduleLog::orderByDesc('ran_at')->paginate(50, ['*'], 'spage')->withQueryString();
        $emailLogs     = OutgoingEmailLog::orderByDesc('created_at')->paginate(50, ['*'], 'epage')->withQueryString();
        $errorLogLines = $this->readErrorLog();

        return view('admin.logs', compact('tab', 'scheduleLogs', 'emailLogs', 'errorLogLines'));
    }

    public function rerunSchedule(int $id)
    {
        $this->requireAdmin();

        $log = ScheduleLog::findOrFail($id);

        // Extract just the first word of the signature as the command name
        $command = explode(' ', trim($log->command))[0];

        try {
            Artisan::call($command);
            $log->increment('count_in_last_hour');
            return back()->with('success', "Command '{$command}' queued.");
        } catch (\Throwable $e) {
            return back()->withErrors(['rerun' => $e->getMessage()]);
        }
    }

    public function downloadScheduleLog()
    {
        $this->requireAdmin();

        $data = json_encode(ScheduleLog::orderByDesc('ran_at')->limit(1000)->get(), JSON_PRETTY_PRINT);

        return response($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="schedule-log.json"');
    }

    public function downloadEmailLog()
    {
        $this->requireAdmin();

        $data = json_encode(OutgoingEmailLog::orderByDesc('created_at')->limit(1000)->get()->makeVisible('mime'), JSON_PRETTY_PRINT);

        return response($data)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="email-log.json"');
    }

    private function readErrorLog(): array
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            return [];
        }

        $size = filesize($path);
        $read = min($size, 200 * 1024); // last 200 KB

        $handle = fopen($path, 'rb');
        fseek($handle, max(0, $size - $read));
        $content = fread($handle, $read);
        fclose($handle);

        $lines = array_reverse(explode("\n", trim($content)));

        // Group into log entries (each starts with [date])
        $entries = [];
        $current = [];
        foreach ($lines as $line) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2}/', $line) && $current) {
                $entries[] = implode("\n", array_reverse($current));
                $current   = [$line];
                if (count($entries) >= 200) break;
            } else {
                $current[] = $line;
            }
        }
        if ($current) {
            $entries[] = implode("\n", array_reverse($current));
        }

        return array_slice($entries, 0, 200);
    }
}
