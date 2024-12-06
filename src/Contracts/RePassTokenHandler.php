<?php

namespace Xraffsarr\LaravelRePass\Contracts;

use Illuminate\Contracts\Auth\CanResetPassword;

interface RePassTokenHandler
{
    public function tokenPayload($email, #[\SensitiveParameter] $token): array;
    public function tokenExists(CanResetPassword $user, #[\SensitiveParameter] $token, #[\SensitiveParameter] $record): bool;
}