<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\VirtualGift;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function wallet()
    {
        $wallet = UserWallet::forUser(auth()->id());
        $sent   = VirtualGift::where('sender_id', auth()->id())->with('recipient')->latest()->limit(20)->get();
        $received = VirtualGift::where('recipient_id', auth()->id())->with('sender')->latest()->limit(20)->get();
        return view('gifts.wallet', compact('wallet', 'sent', 'received'));
    }

    public function send(Request $request, User $user)
    {
        abort_if($user->id === auth()->id(), 422);

        $data = $request->validate([
            'gift_type'    => 'required|in:' . implode(',', array_keys(VirtualGift::$types)),
            'message'      => 'nullable|string|max:200',
            'context_type' => 'nullable|string|max:30',
            'context_id'   => 'nullable|integer',
        ]);

        $type   = VirtualGift::$types[$data['gift_type']];
        $wallet = UserWallet::forUser(auth()->id());

        if (!$wallet->spend($type['coins'])) {
            return response()->json(['error' => 'Insufficient coins'], 422);
        }

        UserWallet::forUser($user->id)->earn($type['coins']);

        $gift = VirtualGift::create([
            'sender_id'    => auth()->id(),
            'recipient_id' => $user->id,
            'gift_type'    => $data['gift_type'],
            'coins'        => $type['coins'],
            'context_type' => $data['context_type'] ?? null,
            'context_id'   => $data['context_id'] ?? null,
            'message'      => $data['message'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'emoji'   => $type['emoji'],
            'label'   => $type['label'],
            'coins'   => $type['coins'],
            'balance' => $wallet->fresh()->coins,
        ]);
    }

    // Demo: award free coins (in production this would be a payment gateway callback)
    public function buyCoins(Request $request)
    {
        $data = $request->validate(['package' => 'required|in:100,500,1200,3000']);
        $wallet = UserWallet::forUser(auth()->id());
        $wallet->earn((int) $data['package']);
        return back()->with('success', $data['package'] . ' coins added to your wallet!');
    }
}
