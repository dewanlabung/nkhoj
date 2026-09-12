<?php

namespace App\Domains\Identity\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorService
{
    public function __construct(private Google2FA $google2fa) {}

    public function generateSetup(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();

        $qrUrl = $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);
        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
        $qrSvg    = (new Writer($renderer))->writeString($qrUrl);

        return compact('secret', 'qrSvg');
    }

    public function confirm(User $user, string $secret, string $code): bool
    {
        if (!$this->google2fa->verifyKey($secret, $code)) {
            return false;
        }

        $user->update([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_enabled'      => true,
            'two_factor_confirmed_at' => now(),
        ]);

        return true;
    }

    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret'       => null,
            'two_factor_enabled'      => false,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function verifyChallenge(User $user, string $code): bool
    {
        $secret = decrypt($user->two_factor_secret);
        return $this->google2fa->verifyKey($secret, $code);
    }
}
