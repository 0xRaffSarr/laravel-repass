<?php

namespace Xraffsarr\LaravelRePass;

use Illuminate\Auth\Passwords\DatabaseTokenRepository as BaseDatabaseRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\ConnectionInterface;
class DatabaseTokenRepository extends BaseDatabaseRepository
{
    protected RePassManager $manager;

    public function __construct(
        ConnectionInterface $connection,
        HasherContract $hasher,
        $table,
        $hashKey,
        RePassManager $manager,
        $expires = 60,
        $throttle = 60,
    ) {
        parent::__construct($connection, $hasher, $table, $hashKey, $expires, $throttle);
        $this->manager = $manager;
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

        return $record &&
            ! $this->tokenExpired($record['created_at']) &&
            $this->manager->getTokenHandler()->tokenExists($user, $token, $record);
    }

}