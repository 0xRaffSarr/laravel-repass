<?php

namespace Xraffsarr\LaravelRePass;

use \Illuminate\Auth\Passwords\DatabaseTokenRepository as BaseDatabaseRepository;
use Illuminate\Support\Carbon;

class DatabaseTokenRepository extends BaseDatabaseRepository
{

    protected function getPayload($email, #[\SensitiveParameter] $token)
    {
        return ['email' => $email, 'token' => $this->hasher->make($token), 'created_at' => new Carbon];
    }

}