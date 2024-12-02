<?php

namespace Xraffsarr\LaravelRePass;

use Illuminate\Auth\Passwords\DatabaseTokenRepository as BaseDatabaseRepository;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;

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

}