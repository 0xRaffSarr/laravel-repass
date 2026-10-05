<?php

namespace Xraffsarr\LaravelRePass\Tests\Feature;

use Laravel\Passport\PassportServiceProvider;
use Xraffsarr\LaravelRePass\DatabaseTokenRepository;
use Xraffsarr\LaravelRePass\Facade\RePass;
use Xraffsarr\LaravelRePass\RePassBrokerManager;
use Xraffsarr\LaravelRePass\RePassServiceProvider;
use Xraffsarr\LaravelRePass\Tests\TestCase;

/**
 * The override must hold whatever the provider order is: RePass registered before Passport
 * here, after Passport in the reversed-order subclass.
 */
class PassportCompatibilityTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [RePassServiceProvider::class, PassportServiceProvider::class];
    }

    public function test_passport_is_loaded_alongside_repass(): void
    {
        $this->assertTrue($this->app->providerIsLoaded(PassportServiceProvider::class));
    }

    public function test_repass_still_owns_the_password_broker(): void
    {
        $this->assertInstanceOf(RePassBrokerManager::class, app('auth.password'));
        $this->assertInstanceOf(DatabaseTokenRepository::class, RePass::getRepository());
    }

    public function test_tokens_work_with_passport_registered(): void
    {
        $user = $this->makeUser();
        $token = RePass::createToken($user);

        $this->assertTrue(RePass::tokenExists($user, $token));
    }
}
