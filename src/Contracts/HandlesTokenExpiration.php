<?php

namespace Xraffsarr\LaravelRePass\Contracts;

use Illuminate\Contracts\Auth\CanResetPassword;

/**
 * Optional companion of RePassTokenHandler for handlers whose credentials expire at different
 * times (e.g. a short-lived OTP next to a longer-lived link token). Without it the repository
 * applies the broker's "expire" setting to every credential.
 */
interface HandlesTokenExpiration
{
    /**
     * Decide whether the stored record is expired for the credential the user submitted.
     * Called instead of the default check, before tokenExists().
     *
     * @param  string  $token  the value submitted by the user
     * @param  array  $record  the stored row (created_at included)
     * @param  int  $defaultExpires  the broker's "expire" setting, in seconds: use it as fallback
     */
    public function tokenExpired(CanResetPassword $user, #[\SensitiveParameter] $token, #[\SensitiveParameter] array $record, int $defaultExpires): bool;
}
