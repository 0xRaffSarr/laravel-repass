<?php

namespace Xraffsarr\LaravelRePass\Handler;

use Illuminate\Support\Carbon;
use Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler;

class RePassDatabaseTokenHandler implements RePassTokenHandler
{

    public function tokenPayload($email, #[\SensitiveParameter] $token): array
    {
        $hasher = app('hash');
        return ['email' => $email, 'token' => $hasher->make($token), 'created_at' => new Carbon];
    }
}