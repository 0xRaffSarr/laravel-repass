<?php

namespace Xraffsarr\LaravelRePass\Tests\Feature;

use InvalidArgumentException;
use Xraffsarr\LaravelRePass\Facade\RePass;
use Xraffsarr\LaravelRePass\Handler\RePassDatabaseTokenHandler;
use Xraffsarr\LaravelRePass\Tests\Fixtures\HandlerWithDependency;
use Xraffsarr\LaravelRePass\Tests\Fixtures\OtpHandler;
use Xraffsarr\LaravelRePass\Tests\TestCase;

class TokenHandlerRegistrationTest extends TestCase
{
    public function test_database_handler_is_the_default(): void
    {
        $this->assertInstanceOf(RePassDatabaseTokenHandler::class, RePass::getTokenHandler());
    }

    public function test_handler_can_be_registered_from_an_instance(): void
    {
        $handler = new OtpHandler();

        RePass::useTokenHandler($handler);

        $this->assertSame($handler, RePass::getTokenHandler());
    }

    public function test_handler_can_be_registered_from_a_class_name(): void
    {
        RePass::useTokenHandler(OtpHandler::class);

        $this->assertInstanceOf(OtpHandler::class, RePass::getTokenHandler());
    }

    public function test_class_name_handlers_are_built_by_the_container(): void
    {
        RePass::useTokenHandler(HandlerWithDependency::class);

        $this->assertNotNull(RePass::getTokenHandler()->hasher);
    }

    public function test_class_that_does_not_implement_the_contract_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RePass::useTokenHandler(\stdClass::class);
    }

    public function test_unknown_class_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RePass::useTokenHandler('Not\\A\\Real\\Handler');
    }

    public function test_rejected_handler_leaves_the_previous_one_active(): void
    {
        RePass::useTokenHandler(OtpHandler::class);

        try {
            RePass::useTokenHandler(\stdClass::class);
        } catch (InvalidArgumentException) {
        }

        $this->assertInstanceOf(OtpHandler::class, RePass::getTokenHandler());
    }
}
