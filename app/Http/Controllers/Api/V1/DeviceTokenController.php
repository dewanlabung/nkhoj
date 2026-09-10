<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'platform'    => 'required|in:ios,android,web',
            'token'       => 'required|string|max:512',
            'app_version' => 'nullable|string|max:20',
        ]);

        DeviceToken::updateOrCreate(
            ['user_id' => $request->user()->id, 'token' => $data['token']],
            [
                'platform'    => $data['platform'],
                'app_version' => $data['app_version'] ?? null,
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['message' => 'Device token registered']);
    }

    public function unregister(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        DeviceToken::where('user_id', $request->user()->id)
            ->where('token', $request->token)
            ->delete();

        return response()->json(['message' => 'Device token removed']);
    }
}
