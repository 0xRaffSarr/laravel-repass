<?php

namespace Xraffsarr\LaravelRePass\Tests\Feature;

use InvalidArgumentException;
use Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler;
use Xraffsarr\LaravelRePass\Facade\RePass;
use Xraffsarr\LaravelRePass\Tests\TestCase;

class BrokerManagerTest extends TestCase
{
    private function repositoryProperty(string $name): mixed
    {
        $repository = RePass::getRepository();

        return (new \ReflectionProperty($repository, $name))->getValue($repository);
    }

    public function test_expire_is_converted_from_minutes_to_seconds(): void
    {
        $this->assertSame(5 * 60, $this->repositoryProperty('expires'));
    }

    public function test_throttle_is_passed_to_the_repository(): void
    {
        config(['auth.passwords.users.throttle' => 90]);

        $this->assertSame(90, $this->repositoryProperty('throttle'));
    }

    public function test_string_config_values_from_the_environment_are_accepted(): void
    {
        config(['auth.passwords.users.expire' => '5', 'auth.passwords.users.throttle' => '90']);

        $this->assertSame(5 * 60, $this->repositoryProperty('expires'));
        $this->assertSame(90, $this->repositoryProperty('throttle'));
    }

    public function test_base64_app_key_is_decoded_for_the_hash_key(): void
    {
        $this->assertSame(str_repeat('k', 32), $this->repositoryProperty('hashKey'));
    }

    public function test_plain_app_key_is_used_as_is(): void
    {
        config(['app.key' => 'plain-key']);

        $this->assertSame('plain-key', $this->repositoryProperty('hashKey'));
    }

    public function test_cache_driver_is_rejected(): void
    {
        config(['auth.passwords.users.driver' => 'cache']);

        $this->expectException(InvalidArgumentException::class);

        app('auth.password')->broker('users');
    }

    public function test_manager_methods_are_reachable_through_the_facade(): void
    {
        $this->assertInstanceOf(RePassTokenHandler::class, RePass::getTokenHandler());
    }

    public function test_broker_methods_are_reachable_through_the_facade(): void
    {
        $user = $this->makeUser();

        $this->assertTrue($user->is(RePass::getUser(['email' => $user->email])));
    }

    public function test_unknown_method_fails_like_on_the_framework_manager(): void
    {
        $this->expectException(\Error::class);

        RePass::doesNotExist();
    }
}
