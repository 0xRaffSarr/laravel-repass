<?php

namespace Xraffsarr\LaravelRePass;

use Illuminate\Auth\Passwords\DatabaseTokenRepository as BaseDatabaseRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\ConnectionInterface;
use Xraffsarr\LaravelRePass\Contracts\HandlesTokenExpiration;

/**
 * Framework database token repository that delegates token creation, the stored payload and
 * token verification to the active RePassTokenHandler.
 */
class DatabaseTokenRepository extends BaseDatabaseRepository
{
    /**
     * @param  int  $expires  seconds a token stays valid (default for handlers without their own rule)
     * @param  int  $throttle  seconds before a new token can be requested for the same user
     */
    public function __construct(
        ConnectionInterface $connection,
        HasherContract $hasher,
        $table,
        $hashKey,
        protected RePassManager $manager,
        $expires = 3600,
        $throttle = 60,
    ) {
        parent::__construct($connection, $hasher, $table, $hashKey, $expires, $throttle);
    }

    /**
     * Build the row stored in the reset table, as defined by the handler.
     */
    protected function getPayload($email, #[\SensitiveParameter] $token)
    {
        return $this->manager->getTokenHandler()->tokenPayload($email, $token);
    }

    /**
     * Generate the token through the handler (a string, or an array for multi-secret handlers).
     */
    public function createNewToken()
    {
        return $this->manager->getTokenHandler()->createToken($this->hashKey);
    }

    /**
     * Same flow as the framework (record found, not expired, token matches), except that
     * expiration and matching can be customised by the handler.
     */
    public function exists(CanResetPasswordContract $user, #[\SensitiveParameter] $token)
    {
        // A missing row casts to an empty array, which is falsy below.
        $record = (array) $this->getTable()->where(
            'email', $user->getEmailForPasswordReset()
        )->first();

        if (! $record) {
            return false;
        }

        $handler = $this->manager->getTokenHandler();

        $expired = $handler instanceof HandlesTokenExpiration
            ? $handler->tokenExpired($user, $token, $record, $this->expires)
            : $this->tokenExpired($record['created_at']);

        return ! $expired && $handler->tokenExists($user, $token, $record);
    }
}