<?php
namespace Xraffsarr\LaravelRePass;

use Illuminate\Support\ServiceProvider;

class RePassServiceProvider extends ServiceProvider
{
    public function register() {
        $this->registerRePass();
    }

    /**
     * @return void
     *
     * @author Raffaele Sarracino
     * @version 1.0.0
     */
    protected function registerRePass()
    {
        $this->app->singleton(RePassManager::class, function ($app) {
            return new RePassManager($app);
        });

        $this->app->extend('auth.password', function ($original, $app) {
            return new RePassBrokerManager($app, $app->make(RePassManager::class));
        });

        $this->app->extend('auth.password.broker', function ($original, $app) {
            return $app->make('auth.password')->broker();
        });
    }

    public function provides() {
        return ['auth.password', 'auth.password.broker'];
    }
}