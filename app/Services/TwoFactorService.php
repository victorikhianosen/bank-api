<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{

    public function generateSecret(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    public function getQRCode(User $user, $secret)
    {
        $google2fa = new Google2FA();

        return $google2fa->getQRCodeInline(
            'AssetMatrix',
            $user->email,
            $secret
        );
    }

    public function verify(string $secret, string $otp): bool
    {
        $google2fa = new Google2FA();

        return $google2fa->verifyKey($secret, $otp);
    }
}