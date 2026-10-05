<?php

namespace Xraffsarr\LaravelRePass\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Xraffsarr\LaravelRePass\RePassServiceProvider;
use Xraffsarr\LaravelRePass\Tests\Fixtures\TestUser;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [RePassServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('auth.providers.users.model', TestUser::class);
        $app['config']->set('auth.passwords.users', [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 5,
            'throttle' => 0,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // "otp" is only used by multi-secret handlers.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->string('otp')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function makeUser(): TestUser
    {
        return TestUser::forceCreate(['name' => 'A', 'email' => 'a@b.it', 'password' => 'x']);
    }
}
