<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ApiAuditLog;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index()
    {
        $tokens = auth()->user()->tokens()->latest()->get();
        return view('account.tokens', compact('tokens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:80',
            'scopes' => 'nullable|array',
            'scopes.*' => 'in:read,write,notifications,admin',
        ]);

        $token = auth()->user()->createToken(
            $data['name'],
            $data['scopes'] ?? ['read']
        );

        return back()->with('new_token', $token->plainTextToken)
                     ->with('success', 'Token created. Copy it now — it will not be shown again.');
    }

    public function destroy(int $id)
    {
        auth()->user()->tokens()->where('id', $id)->delete();
        return back()->with('success', 'Token revoked.');
    }

    public function activity(int $id)
    {
        $token = auth()->user()->tokens()->findOrFail($id);
        $logs  = \DB::table('api_audit_logs')
            ->where('token_id', $id)
            ->latest('created_at')
            ->paginate(30);
        return view('account.token-activity', compact('token', 'logs'));
    }
}
