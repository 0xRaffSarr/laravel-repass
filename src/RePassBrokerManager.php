<?php

namespace Xraffsarr\LaravelRePass;

use Illuminate\Auth\Passwords\PasswordBrokerManager;
use InvalidArgumentException;

/**
 * Only the token repository is replaced: broker creation (user provider, events, timebox)
 * stays inherited from the framework, so framework changes there come for free.
 */
class RePassBrokerManager extends PasswordBrokerManager
{
    public function __construct($app, protected RePassManager $manager)
    {
        parent::__construct($app);
    }

    /**
     * @inheritDoc
     */
    protected function createTokenRepository(array $config)
    {
        if (($config['driver'] ?? null) === 'cache') {
            throw new InvalidArgumentException('The RePass token handler does not support the "cache" driver.');
        }

        $key = $this->app['config']['app.key'];

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return new DatabaseTokenRepository(
            $this->app['db']->connection($config['connection'] ?? null),
            $this->app['hash'],
            $config['table'],
            $key,
            $this->manager,
            ($config['expire'] ?? 60) * 60,
            $config['throttle'] ?? 0,
        );
    }

    /**
     * RePassManager methods (useTokenHandler, getTokenHandler) first, then the default broker.
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->manager, $method)) {
            return $this->manager->{$method}(...$parameters);
        }

        return parent::__call($method, $parameters);
    }
}
