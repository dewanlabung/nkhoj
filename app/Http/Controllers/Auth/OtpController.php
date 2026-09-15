<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $otp = Otp::generate($request->email, 'email_verification');

            Mail::raw("Your OTP verification code is: {$otp->code}\n\nThis code expires in 10 minutes.", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Email Verification - OTP Code');
            });

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email',
                'email' => $request->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $otp = Otp::verify($request->email, $request->code, 'email_verification');

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code',
            ], 422);
        }

        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->input('name', explode('@', $request->email)[0]),
                'email_verified_at' => now(),
            ]
        );

        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
            'user_id' => $user->id,
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $request->merge(['code' => '']);
        return $this->sendOtp($request);
    }
}
