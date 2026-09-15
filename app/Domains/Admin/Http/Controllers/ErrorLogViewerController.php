<?php

namespace App\Domains\Admin\Http\Controllers;

use Opcodesio\LogViewer\Facades\LogViewer;
use Illuminate\Http\Request;

class ErrorLogViewerController extends BaseAdminController
{
    public function index(Request $request)
    {
        $this->requireAdmin();

        try {
            $files = LogViewer::getFiles();

            $selected = $request->query('file', $files->first()?->name ?? null);
            $level = $request->query('level', 'all');
            $search = $request->query('search', '');
            $perPage = (int)($request->query('per_page', 50));
            $page = (int)($request->query('page', 1));

            $logFile = null;
            $logs = [];
            $total = 0;
            $pagination = null;

            if ($selected) {
                $logFile = LogViewer::getFile($selected);

                if ($logFile) {
                    $logEntries = $logFile->logs();

                    if ($level && $level !== 'all') {
                        $logEntries = $logEntries->filter(fn($log) => strtolower($log->level) === strtolower($level));
                    }

                    if ($search) {
                        $logEntries = $logEntries->filter(function($log) use ($search) {
                            $searchLower = strtolower($search);
                            return str_contains(strtolower($log->text), $searchLower) ||
                                   str_contains(strtolower($log->level), $searchLower);
                        });
                    }

                    $total = $logEntries->count();
                    $offset = ($page - 1) * $perPage;

                    $logs = $logEntries
                        ->slice($offset, $perPage)
                        ->values()
                        ->toArray();

                    $pagination = [
                        'total' => $total,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => (int)ceil($total / $perPage),
                        'from' => $offset + 1,
                        'to' => min($offset + $perPage, $total),
                    ];
                }
            }

            return view('admin.error-log-viewer', [
                'files' => $files,
                'selected' => $selected,
                'logFile' => $logFile,
                'logs' => $logs,
                'level' => $level,
                'search' => $search,
                'pagination' => $pagination,
            ]);
        } catch (\Exception $e) {
            return back()->withError('Failed to load logs: ' . $e->getMessage());
        }
    }

    public function download(Request $request)
    {
        $this->requireAdmin();

        try {
            $file = $request->query('file');

            if (!$file) {
                return back()->withError('No file specified');
            }

            $logFile = LogViewer::getFile($file);

            if (!$logFile) {
                return back()->withError('File not found');
            }

            $content = $logFile->content();

            return response()->download(
                stream_get_meta_data(tmpfile())['uri'],
                basename($file),
                ['Content-Type' => 'text/plain']
            )->setContent($content);
        } catch (\Exception $e) {
            return back()->withError('Failed to download log: ' . $e->getMessage());
        }
    }

    public function clear(Request $request)
    {
        $this->requireAdmin();

        $this->validate($request, [
            'file' => 'required|string',
        ]);

        try {
            $file = $request->input('file');
            $logFile = LogViewer::getFile($file);

            if (!$logFile) {
                return back()->withError('File not found');
            }

            file_put_contents($logFile->path, '');

            return back()->with('success', "Log file cleared successfully");
        } catch (\Exception $e) {
            return back()->withError('Failed to clear log: ' . $e->getMessage());
        }
    }
}
