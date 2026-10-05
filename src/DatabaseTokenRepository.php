<?php

namespace Xraffsarr\LaravelRePass;

use Illuminate\Auth\Passwords\DatabaseTokenRepository as BaseDatabaseRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\ConnectionInterface;
use Xraffsarr\LaravelRePass\Contracts\HandlesTokenExpiration;

class DatabaseTokenRepository extends BaseDatabaseRepository
{
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

    protected function getPayload($email, #[\SensitiveParameter] $token)
    {
        return $this->manager->getTokenHandler()->tokenPayload($email, $token);
    }

    public function createNewToken()
    {
        return $this->manager->getTokenHandler()->createToken($this->hashKey);
    }

    public function exists(CanResetPasswordContract $user, #[\SensitiveParameter] $token)
    {
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