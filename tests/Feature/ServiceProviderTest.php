<?php

namespace Xraffsarr\LaravelRePass\Tests\Feature;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Xraffsarr\LaravelRePass\DatabaseTokenRepository;
use Xraffsarr\LaravelRePass\Facade\RePass;
use Xraffsarr\LaravelRePass\RePassBrokerManager;
use Xraffsarr\LaravelRePass\RePassManager;
use Xraffsarr\LaravelRePass\RePassServiceProvider;
use Xraffsarr\LaravelRePass\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_replaces_the_framework_broker_manager(): void
    {
        $this->assertInstanceOf(RePassBrokerManager::class, app('auth.password'));
        $this->assertSame(app('auth.password'), app('auth.password'));
    }

    public function test_broker_is_the_standard_framework_broker_with_the_handler_aware_repository(): void
    {
        $this->assertInstanceOf(PasswordBroker::class, app('auth.password.broker'));
        $this->assertInstanceOf(DatabaseTokenRepository::class, RePass::getRepository());
    }

    public function test_password_and_repass_facades_share_the_same_broker(): void
    {
        $this->assertSame(Password::broker(), RePass::broker());
    }

    public function test_manager_is_a_singleton(): void
    {
        $this->assertSame(app(RePassManager::class), app(RePassManager::class));
    }

    public function test_provider_is_deferred_and_declares_the_overridden_services(): void
    {
        $provider = new RePassServiceProvider($this->app);

        $this->assertTrue($provider->isDeferred());
        $this->assertEqualsCanonicalizing(
            [RePassManager::class, 'auth.password', 'auth.password.broker'],
            $provider->provides(),
        );
    }

    public function test_timebox_duration_config_reaches_the_broker(): void
    {
        config(['auth.timebox_duration' => 12345]);

        $broker = app('auth.password')->broker('users');
        $property = new \ReflectionProperty($broker, 'timeboxDuration');

        $this->assertSame(12345, $property->getValue($broker));
    }
}
