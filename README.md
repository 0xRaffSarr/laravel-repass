# laravel-repass

Replaces Laravel's password-reset token handling with a pluggable handler, so the reset flow
(token format, extra OTP column, verification rule) can differ from the framework default.
The broker, user provider, events and timebox stay the framework's own.

Requires PHP 8.2+ and Laravel 12 or 13. Auto-discovered; the `RePass` facade is a drop-in for `Password`.

## Custom handler

Implement `Xraffsarr\LaravelRePass\Contracts\RePassTokenHandler` and register it, e.g. in a service provider:

```php
RePass::useTokenHandler(MyTokenHandler::class); // class name (resolved from the container) or instance
```

- `createToken(string $hashKey): string|array` — the value is passed to `tokenPayload()` and to the notification.
- `tokenPayload($email, $token): array` — the row stored in the reset table (add your own columns).
- `tokenExists($user, $token, array $record): bool` — `$token` is what the user submitted.

### Different expirations (optional)

By default the broker's `expire` setting applies to every credential. A handler that also implements
`Contracts\HandlesTokenExpiration` decides it itself, per submitted credential, and receives the default
(`$defaultExpires`, seconds) as fallback:

```php
public function tokenExpired($user, $token, array $record, int $defaultExpires): bool
{
    $seconds = $this->looksLikeOtp($token) ? 120 : $defaultExpires;

    return Carbon::parse($record['created_at'])->addSeconds($seconds)->isPast();
}
```

Set `expire` to the **longest** duration: `auth:clear-resets` prunes by that value alone.

`useTokenHandler()` throws `InvalidArgumentException` if the class does not implement the contract.
Only the `database` driver is supported; a `cache` driver config throws.

## Upgrading from 1.x

- `RePassBroker` and `CacheTokenRepository` are removed; the framework's `PasswordBroker` is used.
- `createToken()` must declare `: string|array`, `tokenExists()` takes `array $record`.
- `auth.timebox_duration` is now honoured.
- PHP 8.2+, Laravel 12/13 only.

## Tests

`composer test` (needs `pdo_sqlite`).
