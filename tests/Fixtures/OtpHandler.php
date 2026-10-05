<?php

namespace Xraffsarr\LaravelRePass\Tests\Fixtures;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Hash;
use Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler;

/** Multi-secret handler: a link token plus an OTP, both accepted as the submitted credential. */
class OtpHandler implements RePassTokenHandler
{
    public function createToken(string $hashKey): string|array
    {
        return ['token' => 'link-token', 'otp' => '123456'];
    }

    public function tokenPayload($email, #[\SensitiveParameter] $token): array
    {
        return [
            'email' => $email,
            'token' => Hash::make($token['token']),
            'otp' => Hash::make($token['otp']),
            'created_at' => now(),
        ];
    }

    public function tokenExists(CanResetPassword $user, #[\SensitiveParameter] $token, #[\SensitiveParameter] array $record): bool
    {
        return Hash::check($token, $record['token']) || Hash::check($token, $record['otp']);
    }
}
