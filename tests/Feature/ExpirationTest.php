<?php

namespace Xraffsarr\LaravelRePass\Tests\Feature;

use Xraffsarr\LaravelRePass\Facade\RePass;
use Xraffsarr\LaravelRePass\Tests\Fixtures\ExpiringOtpHandler;
use Xraffsarr\LaravelRePass\Tests\Fixtures\OtpHandler;
use Xraffsarr\LaravelRePass\Tests\TestCase;

class ExpirationTest extends TestCase
{
    public function test_default_expiration_applies_to_the_default_handler(): void
    {
        $user = $this->makeUser();
        $token = RePass::createToken($user);

        $this->travel(4)->minutes();
        $this->assertTrue(RePass::tokenExists($user, $token));

        $this->travel(2)->minutes();
        $this->assertFalse(RePass::tokenExists($user, $token));
    }

    public function test_handler_without_the_expiration_contract_keeps_the_default_rule(): void
    {
        RePass::useTokenHandler(OtpHandler::class);
        $user = $this->makeUser();
        RePass::createToken($user);

        $this->travel(3)->minutes();
        $this->assertTrue(RePass::tokenExists($user, '123456'));

        $this->travel(3)->minutes();
        $this->assertFalse(RePass::tokenExists($user, '123456'));
    }

    public function test_handler_can_apply_a_different_expiration_per_credential(): void
    {
        RePass::useTokenHandler(ExpiringOtpHandler::class);
        $user = $this->makeUser();
        RePass::createToken($user);

        $this->travel(3)->minutes();

        $this->assertFalse(RePass::tokenExists($user, '123456'), 'the OTP expires after 2 minutes');
        $this->assertTrue(RePass::tokenExists($user, 'link-token'), 'the link token is valid for the 5 minute default');

        $this->travel(3)->minutes();

        $this->assertFalse(RePass::tokenExists($user, 'link-token'), 'the link token expires at the default');
    }

    public function test_handler_receives_the_broker_default_in_seconds(): void
    {
        $handler = new ExpiringOtpHandler();
        RePass::useTokenHandler($handler);
        $user = $this->makeUser();
        RePass::createToken($user);

        RePass::tokenExists($user, 'link-token');

        $this->assertSame(5 * 60, $handler->receivedDefault);
    }

    public function test_expiration_handler_is_not_called_without_a_record(): void
    {
        $handler = new ExpiringOtpHandler();
        RePass::useTokenHandler($handler);

        $this->assertFalse(RePass::tokenExists($this->makeUser(), 'link-token'));
        $this->assertNull($handler->receivedDefault);
    }
}
