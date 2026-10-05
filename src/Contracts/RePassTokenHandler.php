<?php

namespace Xraffsarr\LaravelRePass\Contracts;

use Illuminate\Contracts\Auth\CanResetPassword;

interface RePassTokenHandler
{
    /**
     * Build the token record stored in the reset table.
     *
     * @param  string  $email
     * @param  string|array  $token  whatever createToken() returned
     */
    public function tokenPayload($email, #[\SensitiveParameter] $token): array;

    /**
     * Check the token the user submitted against the stored record.
     *
     * @param  string  $token  the value submitted by the user (not the createToken() result)
     * @param  array  $record  the stored row
     */
    public function tokenExists(CanResetPassword $user, #[\SensitiveParameter] $token, #[\SensitiveParameter] array $record): bool;

    /**
     * Generate a new token. A plain string, or an array when the handler needs more than one
     * secret (e.g. link token + OTP): the same value is handed to tokenPayload() and to the notification.
     */
    public function createToken(string $hashKey): string|array;
}
