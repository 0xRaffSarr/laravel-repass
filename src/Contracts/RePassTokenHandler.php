<?php

namespace Xraffsarr\LaravelRePass\Contracts;

interface RePassTokenHandler
{
    public function tokenPayload($email, #[\SensitiveParameter] $token): array;
}