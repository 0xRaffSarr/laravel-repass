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
        // One handler for the whole application.
        $this->app->singleton(RePassManager::class, fn ($app) => new RePassManager($app));

        // Replaces the framework's broker manager (same service id, so Password and RePass facades both use it).
        $this->app->singleton('auth.password', fn ($app) => new RePassBrokerManager($app, $app->make(RePassManager::class)));

        // Same binding as the framework: the default broker of the manager above.
        $this->app->bind('auth.password.broker', fn ($app) => $app->make('auth.password')->broker());
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [RePassManager::class, 'auth.password', 'auth.password.broker'];
    }
}
