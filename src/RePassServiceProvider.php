<?php

namespace Xraffsarr\LaravelRePass;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Deferred, like the framework's PasswordResetServiceProvider: both providers claim the same
 * services and the one loaded later wins, so the override does not depend on registration order.
 */
class RePassServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(RePassManager::class, fn ($app) => new RePassManager($app));

        $this->app->singleton('auth.password', fn ($app) => new RePassBrokerManager($app, $app->make(RePassManager::class)));

        $this->app->bind('auth.password.broker', fn ($app) => $app->make('auth.password')->broker());
    }

    public function provides(): array
    {
        return [RePassManager::class, 'auth.password', 'auth.password.broker'];
    }
}
