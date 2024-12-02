<?php

namespace Xraffsarr\LaravelRePass;

use Closure;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use InvalidArgumentException;

class RePassBrokerManager extends PasswordBrokerManager
{

    protected RePassManager $manager;

    public function __construct($app, RePassManager $manager)
    {
        parent::__construct($app);
        $this->manager = $manager;
    }

    /**
     * @param $name
     * @return \Xraffsarr\LaravelRePass\RePassBroker
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Password resetter [{$name}] is not defined.");
        }

        return new RePassBroker(
            $this->createTokenRepository($config),
            $this->app['auth']->createUserProvider($config['provider'] ?? null),
            $this->app['events'] ?? null,
        );
    }

    /**
     * @inheritDoc
     */
    protected function createTokenRepository(array $config) {
        $key = $this->app['config']['app.key'];

        if(str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if(isset($config['driver']) && $config['driver'] == 'cache') {
            return new CacheTokenRepository(
                $this->app['cache']->store($config['store'] ?? null),
                $this->app['hash'],
                $key,
                ($config['expire'] ?? 60) * 60,
                $config['throttle'] ?? 0,
                $config['prefix'] ?? '',
            );
        }

        return new DatabaseTokenRepository(
            $this->app['db']->connection($config['connection'] ?? null),
            $this->app['hash'],
            $config['table'],
            $key,
            $this->manager,
            $config['expire'],
            $config['throttle'] ?? 0
        );
    }


}