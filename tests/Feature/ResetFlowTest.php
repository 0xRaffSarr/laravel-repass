<?php

namespace Xraffsarr\LaravelRePass\Tests\Feature;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Xraffsarr\LaravelRePass\Facade\RePass;
use Xraffsarr\LaravelRePass\Tests\Fixtures\OtpHandler;
use Xraffsarr\LaravelRePass\Tests\TestCase;

class ResetFlowTest extends TestCase
{
    private function resetWith(string $email, string $token): string
    {
        return RePass::reset(
            ['email' => $email, 'token' => $token, 'password' => 'new-pass'],
            fn ($user, $password) => $user->forceFill(['password' => Hash::make($password)])->save(),
        );
    }

    public function test_reset_link_notification_carries_the_token(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $status = RePass::sendResetLink(['email' => $user->email]);

        $this->assertSame(PasswordBroker::RESET_LINK_SENT, $status);
        Notification::assertSentTo($user, ResetPassword::class, fn ($n) => is_string($n->token) && strlen($n->token) === 64);
    }

    public function test_reset_link_notification_carries_array_tokens_from_custom_handlers(): void
    {
        RePass::useTokenHandler(OtpHandler::class);
        Notification::fake();
        $user = $this->makeUser();

        RePass::sendResetLink(['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, fn ($n) => $n->token === ['token' => 'link-token', 'otp' => '123456']);
    }

    public function test_reset_link_is_not_sent_to_unknown_users(): void
    {
        Notification::fake();

        $status = RePass::sendResetLink(['email' => 'nobody@b.it']);

        $this->assertSame(PasswordBroker::INVALID_USER, $status);
        Notification::assertNothingSent();
    }

    public function test_second_request_within_the_throttle_window_is_throttled(): void
    {
        config(['auth.passwords.users.throttle' => 60]);
        Notification::fake();
        $user = $this->makeUser();

        RePass::sendResetLink(['email' => $user->email]);
        $status = RePass::sendResetLink(['email' => $user->email]);

        $this->assertSame(PasswordBroker::RESET_THROTTLED, $status);
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_reset_with_a_valid_token_changes_the_password_and_consumes_the_token(): void
    {
        $user = $this->makeUser();
        $token = RePass::createToken($user);

        $status = $this->resetWith($user->email, $token);

        $this->assertSame(PasswordBroker::PASSWORD_RESET, $status);
        $this->assertTrue(Hash::check('new-pass', $user->fresh()->password));
        $this->assertSame(0, DB::table('password_reset_tokens')->count());
        $this->assertFalse(RePass::tokenExists($user, $token));
    }

    public function test_reset_with_a_wrong_token_leaves_the_password_untouched(): void
    {
        $user = $this->makeUser();
        RePass::createToken($user);

        $status = $this->resetWith($user->email, 'wrong');

        $this->assertSame(PasswordBroker::INVALID_TOKEN, $status);
        $this->assertSame('x', $user->fresh()->password);
    }

    public function test_reset_with_an_expired_token_is_rejected(): void
    {
        $user = $this->makeUser();
        $token = RePass::createToken($user);

        $this->travel(6)->minutes();

        $this->assertSame(PasswordBroker::INVALID_TOKEN, $this->resetWith($user->email, $token));
    }

    public function test_reset_can_use_the_otp_of_a_multi_secret_handler(): void
    {
        RePass::useTokenHandler(OtpHandler::class);
        $user = $this->makeUser();
        RePass::createToken($user);

        $this->assertSame(PasswordBroker::PASSWORD_RESET, $this->resetWith($user->email, '123456'));
        $this->assertTrue(Hash::check('new-pass', $user->fresh()->password));
    }
}
