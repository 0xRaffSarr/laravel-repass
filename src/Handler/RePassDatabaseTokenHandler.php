<?php

namespace Xraffsarr\LaravelRePass\Handler;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Carbon;
use Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler;

class RePassDatabaseTokenHandler implements RePassTokenHandler
{

    public function tokenPayload($email, #[\SensitiveParameter] $token): array
    {
        $hasher = app('hash');
        return ['email' => $email, 'token' => $hasher->make($token), 'created_at' => new Carbon];
    }

    public function tokenExists(CanResetPassword $user, #[\SensitiveParameter] $token, #[\SensitiveParameter] $record): bool {
        $hasher = app('hash');
        return $hasher->check($token, $record['token']);
    }
}