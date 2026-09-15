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
            'two_factor_secret'          => encrypt($secret),
            'two_factor_enabled'         => true,
            'two_factor_confirmed_at'    => now(),
            'two_factor_recovery_codes'  => $this->generateRecoveryCodes(),
        ]);

        return true;
    }

    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret'          => null,
            'two_factor_enabled'         => false,
            'two_factor_confirmed_at'    => null,
            'two_factor_recovery_codes'  => null,
        ]);
    }

    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    public function regenerateRecoveryCodes(User $user): array
    {
        $codes = $this->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => $codes]);
        return $codes;
    }

    public function useRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $code  = strtoupper(trim($code));

        $index = array_search($code, $codes, true);
        if ($index === false) return false;

        // Remove used code
        array_splice($codes, $index, 1);
        $user->update(['two_factor_recovery_codes' => $codes]);

        return true;
    }

    public function verifyChallenge(User $user, string $code): bool
    {
        $secret = decrypt($user->two_factor_secret);
        return $this->google2fa->verifyKey($secret, $code);
    }
}
