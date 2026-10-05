<?php

namespace Xraffsarr\LaravelRePass\Tests\Feature;

use Laravel\Passport\PassportServiceProvider;
use Xraffsarr\LaravelRePass\RePassServiceProvider;

class PassportCompatibilityReversedOrderTest extends PassportCompatibilityTest
{
    protected function getPackageProviders($app): array
    {
        return [PassportServiceProvider::class, RePassServiceProvider::class];
    }
}
