<?php

namespace Xraffsarr\LaravelRePass;

use Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler;
use Xraffsarr\LaravelRePass\Handler\RePassDatabaseTokenHandler;

class RePassManager
{
    protected $app;
    protected RePassTokenHandler  $tokenHandler;

    public function __construct($app) {
        $this->app = $app;
        $this->tokenHandler = new RePassDatabaseTokenHandler();
    }


    /**
     * @param $handler
     * @return void
     *
     * @author Raffaele Sarracino
     * @version 1.0.0
     */
    public function useTokenHandler($handler): void
    {
        if(is_string($handler) && in_array(RePassTokenHandler::class, class_implements($handler))) {
            $this->tokenHandler = new $handler();
        }
        else if($handler instanceof RePassTokenHandler) {
            $this->tokenHandler = $handler;
        }
    }

    /**
     * @return RePassTokenHandler
     *
     * @author Raffaele Sarracino
     * @version 1.0.0
     */
    public function getTokenHandler(): RePassTokenHandler {
        return $this->tokenHandler;
    }
}