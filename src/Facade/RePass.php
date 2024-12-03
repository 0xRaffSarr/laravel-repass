<?php

namespace Xraffsarr\LaravelRePass\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Xraffsarr\LaravelRePass\RePassBroker broker(string|null $name = null)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static string sendResetLink(array $credentials, \Closure|null $callback = null)
 * @method static mixed reset(array $credentials, \Closure $callback)
 * @method static \Illuminate\Contracts\Auth\CanResetPassword|null getUser(array $credentials)
 * @method static string createToken(\Illuminate\Contracts\Auth\CanResetPassword $user)
 * @method static void deleteToken(\Illuminate\Contracts\Auth\CanResetPassword $user)
 * @method static bool tokenExists(\Illuminate\Contracts\Auth\CanResetPassword $user, string $token)
 * @method static \Illuminate\Auth\Passwords\TokenRepositoryInterface getRepository()
 * @method static void useTokenHandler(\Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler|string $handler)
 * @method static \Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler getTokenHandler()
 *
 * @see \Xraffsarr\LaravelRePass\RePassBrokerManager
 * @see \Xraffsarr\LaravelRePass\RePassBroker
 * @see \Xraffsarr\LaravelRePass\RePassManager
 */
class RePass extends Facade
{
    protected static function getFacadeAccessor() {
        return 'auth.password';
    }
}