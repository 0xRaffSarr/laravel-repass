<?php
namespace Xraffsarr\LaravelRePass;

use Illuminate\Support\ServiceProvider;

class RePassServiceProvider extends ServiceProvider
{
    public function boot() {
        $this->registerRePass();
    }

    protected function registerRePass()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new RePassBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
           return $app->make('auth.password')->broker();
        });
    }

    public function provides() {
        return ['auth.password', 'auth.password.broker'];
    }
}