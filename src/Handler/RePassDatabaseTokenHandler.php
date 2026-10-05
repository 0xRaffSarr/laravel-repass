<?php

namespace Xraffsarr\LaravelRePass\Handler;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Str;
use Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler;

class RePassDatabaseTokenHandler implements RePassTokenHandler
{
    public function createToken(string $hashKey): string
    {
        return hash_hmac('sha256', Str::random(40), $hashKey);
    }

    public function tokenPayload($email, #[\SensitiveParameter] $token): array
    {
        return ['email' => $email, 'token' => app('hash')->make($token), 'created_at' => now()];
    }

    public function tokenExists(CanResetPassword $user, #[\SensitiveParameter] $token, #[\SensitiveParameter] array $record): bool
    {
        return app('hash')->check($token, $record['token']);
    }
}
