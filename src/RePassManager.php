<?php

namespace Xraffsarr\LaravelRePass;

use InvalidArgumentException;
use Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler;
use Xraffsarr\LaravelRePass\Handler\RePassDatabaseTokenHandler;

/**
 * Holds the token handler shared by every broker. Registered as a singleton.
 */
class RePassManager
{
    protected RePassTokenHandler $tokenHandler;

    public function __construct(protected $app)
    {
        $this->tokenHandler = new RePassDatabaseTokenHandler();
    }

    /**
     * @param  RePassTokenHandler|class-string<RePassTokenHandler>  $handler  class names are resolved from the container
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
