<?php

declare(strict_types=1);

namespace Xraffsarr\LaravelRePass;

use Illuminate\Auth\Passwords\PasswordBrokerManager;
use InvalidArgumentException;

/**
 * Only the token repository is replaced: broker creation (user provider, events, timebox)
 * stays inherited from the framework, so framework changes there come for free.
 */
class RePassBrokerManager extends PasswordBrokerManager
{
    /**
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  RePassManager  $manager  supplies the active token handler to the repositories
     */
    public function __construct($app, protected RePassManager $manager)
    {
        parent::__construct($app);
    }

    /**
     * Build the repository for a broker config (auth.passwords.*). Mirrors the framework method,
     * but returns the handler-aware database repository.
     *
     * @throws InvalidArgumentException when the config asks for the cache driver
     */
    protected function createTokenRepository(array $config)
    {
        // The cache repository stores hashed tokens itself and cannot delegate to a handler.
        if (($config['driver'] ?? null) === 'cache') {
            throw new InvalidArgumentException('The RePass token handler does not support the "cache" driver.');
        }

        // Same key derivation as the framework: the HMAC key is the decoded app key.
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
            // "expire" is in minutes in the config, the repository wants seconds.
            (int) ($config['expire'] ?? 60) * 60,
            (int) ($config['throttle'] ?? 0),
        );
    }

    /**
     * RePassManager methods (useTokenHandler, getTokenHandler) first, then the default broker,
     * so the facade exposes both. Unknown methods fail as they do on the framework manager.
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->manager, $method)) {
            return $this->manager->{$method}(...$parameters);
        }

        return parent::__call($method, $parameters);
    }
}
