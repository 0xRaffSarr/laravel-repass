<?php

namespace Xraffsarr\LaravelRePass\Tests\Fixtures;

use Illuminate\Contracts\Hashing\Hasher;
use Xraffsarr\LaravelRePass\Handler\RePassDatabaseTokenHandler;

/** Proves handler classes are built by the container, so constructor dependencies are injected. */
class HandlerWithDependency extends RePassDatabaseTokenHandler
{
    public function __construct(public Hasher $hasher)
    {
    }
}
