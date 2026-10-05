<?php

namespace Xraffsarr\LaravelRePass\Tests\Fixtures;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Carbon;
use Xraffsarr\LaravelRePass\Contracts\HandlesTokenExpiration;

/** OTP valid for 2 minutes, link token valid for the broker default. Records the default it receives. */
class ExpiringOtpHandler extends OtpHandler implements HandlesTokenExpiration
{
    public ?int $receivedDefault = null;

    public function tokenExpired(CanResetPassword $user, #[\SensitiveParameter] $token, #[\SensitiveParameter] array $record, int $defaultExpires): bool
    {
        $this->receivedDefault = $defaultExpires;

        $seconds = $token === '123456' ? 120 : $defaultExpires;

        return Carbon::parse($record['created_at'])->addSeconds($seconds)->isPast();
    }
}
