<?php

namespace Xraffsarr\LaravelRePass\Tests\Fixtures;

use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;

/** The framework base user lacks Notifiable, which the reset notification needs. */
class TestUser extends User
{
    use Notifiable;

    protected $table = 'users';
}
