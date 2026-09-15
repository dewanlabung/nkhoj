<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Jobs\ExportAccountData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DataPrivacyController extends Controller
{
    public function index()
    {
        return view('account.data-privacy', ['user' => auth()->user()]);
    }

    public function requestExport(Request $request)
    {
        $user = auth()->user();

        if ($user->data_export_requested_at && $user->data_export_requested_at->gt(now()->subHours(24))) {
            return back()->with('info', 'Export already requested. Check your email within a few minutes.');
        }

        $user->update([
            'data_export_requested_at' => now(),
            'data_export_ready_at'     => null,
            'data_export_token'        => null,
        ]);

        ExportAccountData::dispatch($user->id);

        return back()->with('success', 'Export started. Refresh this page in a moment — a download link will appear here when ready (an email will also be sent if mail is configured).');
    }

    public function download(Request $request)
    {
        $token = $request->query('token');
        $user  = auth()->user();

        if (!$token || $user->data_export_token !== $token) {
            abort(403, 'Invalid or expired export link.');
        }

        if (!$user->data_export_ready_at || $user->data_export_ready_at->lt(now()->subHours(24))) {
            abort(410, 'This export link has expired. Please request a new one.');
        }

        $path = "exports/user-{$user->id}-{$token}.json";
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Export file not found.');
        }

        return Storage::disk('local')->download($path, 'nkhoj-account-data.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function requestDeletion(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);
        $user = auth()->user();

        if ($user->isPendingDeletion()) {
            return back()->with('info', 'Account deletion already scheduled.');
        }

        $user->update(['deletion_requested_at' => now()]);

        return back()->with('success', 'Account deletion scheduled. Your account will be permanently deleted in 30 days. You can cancel any time before then.');
    }

    public function cancelDeletion(Request $request)
    {
        auth()->user()->update(['deletion_requested_at' => null]);
        return back()->with('success', 'Account deletion cancelled. Your account is safe.');
    }
}
