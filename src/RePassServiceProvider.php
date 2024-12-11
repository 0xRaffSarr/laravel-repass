<?php
namespace Xraffsarr\LaravelRePass;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class RePassServiceProvider extends ServiceProvider implements DeferrableProvider
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

        if($this->app->has('auth.password')) {
            $this->app->extend('auth.password', function ($original, $app) {
                return new RePassBrokerManager($app, $app->make(RePassManager::class));
            });
        }
        else {
            $this->app->singleton('auth.password', function ($app) {
                return new RePassBrokerManager($app, $app->male(RePassManager::class));
            });
        }

        if($this->app->has('auth.password.broker')) {
            $this->app->extend('auth.password.broker', function ($original, $app) {
                return $app->make('auth.password')->broker();
            });
        }
        else {
            $this->app->singleton('auth.password.broker', function ($app) {
                return $app->make('auth.password')->broker();
            });
        }
    }

    public function provides() {
        return ['auth.password', 'auth.password.broker'];
    }
}