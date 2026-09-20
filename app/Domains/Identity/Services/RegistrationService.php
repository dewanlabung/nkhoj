<?php

namespace App\Domains\Identity\Services;

use App\Events\UserCreated;
use App\Mail\EmailVerificationLinkMail;
use App\Models\UserEngagement\User;
use App\Services\SiteSettingsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class RegistrationService
{
    public function register(array $data, bool $requireEmailConfirmation = false): User
    {
        $user = User::create([
            'uuid'              => Str::uuid(),
            'name'              => $data['name'],
            'username'          => $data['username'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'role'              => 'author',
            'email_verified_at' => $requireEmailConfirmation ? null : now(),
        ]);

        event(new UserCreated($user, $data));

        if ($requireEmailConfirmation) {
            $method = app(SiteSettingsService::class)->get()['auth']['email_verification_method'] ?? 'both';

            if (in_array($method, ['both', 'otp_only'])) {
                event(new Registered($user)); // triggers sendEmailVerificationNotification → OTP
            }

            if (in_array($method, ['both', 'link_only'])) {
                $url = URL::temporarySignedRoute(
                    'verify-email.link',
                    now()->addHours(24),
                    ['id' => $user->id, 'hash' => sha1($user->email)]
                );
                Mail::to($user->email)->send(new EmailVerificationLinkMail($user, $url));
            }
        }

        return $user;
    }
}
