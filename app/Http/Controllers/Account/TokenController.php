<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ApiAuditLog;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index()
    {
        try {
            $tokens = auth()->user()->tokens()->latest()->get();
        } catch (\Throwable) {
            $tokens = collect();
        }
        return view('account.tokens', compact('tokens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:80',
            'scopes' => 'nullable|array',
            'scopes.*' => 'in:read,write,notifications,admin',
        ]);

        try {
            $token = auth()->user()->createToken(
                $data['name'],
                $data['scopes'] ?? ['read']
            );
            return back()->with('new_token', $token->plainTextToken)
                         ->with('success', 'Token created. Copy it now — it will not be shown again.');
        } catch (\Throwable) {
            return back()->with('error', 'Could not create token — run migrations first.');
        }
    }

    public function destroy(int $id)
    {
        try {
            auth()->user()->tokens()->where('id', $id)->delete();
        } catch (\Throwable) {}
        return back()->with('success', 'Token revoked.');
    }

    public function activity(int $id)
    {
        try {
            $token = auth()->user()->tokens()->findOrFail($id);
            $logs  = \DB::table('api_audit_logs')
                ->where('token_id', $id)
                ->latest('created_at')
                ->paginate(30);
        } catch (\Throwable) {
            $token = (object)['name' => 'Token #' . $id];
            $logs  = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30);
        }
        return view('account.token-activity', compact('token', 'logs'));
    }
}
