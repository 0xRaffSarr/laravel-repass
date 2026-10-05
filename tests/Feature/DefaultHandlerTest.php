<?php

namespace Xraffsarr\LaravelRePass\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Xraffsarr\LaravelRePass\Facade\RePass;
use Xraffsarr\LaravelRePass\Tests\TestCase;

class DefaultHandlerTest extends TestCase
{
    public function test_token_is_a_64_char_hex_string(): void
    {
        $token = RePass::createToken($this->makeUser());

        $this->assertSame(64, strlen($token));
        $this->assertTrue(ctype_xdigit($token));
    }

    public function test_only_the_hash_of_the_token_is_stored(): void
    {
        $user = $this->makeUser();
        $token = RePass::createToken($user);

        $row = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        $this->assertNotSame($token, $row->token);
        $this->assertTrue(Hash::check($token, $row->token));
        $this->assertNotNull($row->created_at);
    }

    public function test_valid_token_is_accepted_and_wrong_token_is_rejected(): void
    {
        $user = $this->makeUser();
        $token = RePass::createToken($user);

        $this->assertTrue(RePass::tokenExists($user, $token));
        $this->assertFalse(RePass::tokenExists($user, 'wrong'));
    }

    public function test_token_of_a_user_without_a_record_is_rejected(): void
    {
        $this->assertFalse(RePass::tokenExists($this->makeUser(), 'anything'));
    }
}
