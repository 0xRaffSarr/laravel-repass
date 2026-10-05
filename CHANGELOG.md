# Changelog

## 2.0.0

### Breaking changes

- Requires PHP 8.2+ and Laravel 12 or 13 (Laravel 10 and 11 are no longer supported).
- `RePassBroker` is removed: the framework's `PasswordBroker` is used.
- `CacheTokenRepository` is removed. A broker configured with `driver: cache` now throws an
  `InvalidArgumentException` instead of silently ignoring the token handler.
- `RePassTokenHandler::createToken()` must declare the return type `string|array`, and
  `tokenExists()` receives the stored record as `array`.
- `RePass::useTokenHandler()` throws an `InvalidArgumentException` for classes that do not implement
  `RePassTokenHandler` (it used to ignore them). Class names are now resolved from the container.
- Unknown methods called on the facade fail with PHP's native error instead of a `http\Exception\BadMethodCallException`.

### Added

- `Contracts\HandlesTokenExpiration`: optional interface for handlers whose credentials expire at
  different times (for example an OTP next to a link token). It receives the broker's `expire`
  setting, in seconds, as fallback.
- Test suite (Orchestra Testbench) and a CI matrix for PHP 8.2-8.4 and Laravel 12/13, including a
  compatibility check with Laravel Passport.

### Fixed

- `auth.timebox_duration` is honoured again: the broker is created by the framework instead of a copy
  of its code.
- The service provider is deferred like the framework's, so overriding `auth.password` no longer
  depends on the provider registration order.
- The default handler uses `now()`, so it follows `Carbon::setTestNow()` / `travel()`.
- The broker's `expire` and `throttle` config values are cast to integers, so values read from `.env` work
  with strict types.

### Internal

- `declare(strict_types=1)` in every source file, docblocks and inline comments in English.

## 1.0.0

Initial stable release: password reset with a pluggable token handler for Laravel 11 and 12.
