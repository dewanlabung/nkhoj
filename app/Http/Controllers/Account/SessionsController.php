<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function index()
    {
        $currentSid = session()->getId();
        try {
            $sessions = auth()->user()->userSessions()->get();
        } catch (\Throwable) {
            $sessions = collect();
        }
        return view('account.sessions', compact('sessions', 'currentSid'));
    }

    public function destroy(Request $request, int $id)
    {
        $session = auth()->user()->userSessions()->findOrFail($id);

        // If revoking current session, log out
        if ($session->session_id === session()->getId()) {
            Auth::logout();
            $session->delete();
            return redirect('/login')->with('success', 'Signed out.');
        }

        $session->delete();
        return back()->with('success', 'Session revoked.');
    }

    public function destroyAll(Request $request)
    {
        $currentSid = session()->getId();
        auth()->user()->userSessions()
            ->where('session_id', '!=', $currentSid)
            ->delete();

        return back()->with('success', 'All other sessions revoked.');
    }
}
