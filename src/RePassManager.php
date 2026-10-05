<?php

declare(strict_types=1);

namespace Xraffsarr\LaravelRePass;

use InvalidArgumentException;
use Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler;
use Xraffsarr\LaravelRePass\Handler\RePassDatabaseTokenHandler;

/**
 * Holds the token handler shared by every broker. Registered as a singleton.
 */
class RePassManager
{
    /** The handler used by every repository: swapped at runtime, so repositories read it lazily. */
    protected RePassTokenHandler $tokenHandler;

    public function __construct(protected $app)
    {
        // Default behaviour is the framework one until the application registers its own handler.
        $this->tokenHandler = new RePassDatabaseTokenHandler();
    }

    /**
     * @param  RePassTokenHandler|string  $handler  a handler, or the name of a handler class (resolved from the container)
     *
     * @throws InvalidArgumentException when the handler does not implement RePassTokenHandler
     */
    public function useTokenHandler(RePassTokenHandler|string $handler): void
    {
        if (is_string($handler)) {
            if (! is_a($handler, RePassTokenHandler::class, true)) {
                throw new InvalidArgumentException("[{$handler}] must implement ".RePassTokenHandler::class.'.');
            }

            $handler = $this->app->make($handler);
        }

        $this->tokenHandler = $handler;
    }

    /**
     * The active handler: the database one unless useTokenHandler() replaced it.
     */
    public function getTokenHandler(): RePassTokenHandler
    {
        return $this->tokenHandler;
    }
}
