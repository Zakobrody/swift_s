<?php

namespace App\tests\Unit\Entity;

use App\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testWhetherToForcePasswordChangeAfterFirstLoginIsTrue()
    {
        $user = new User();
        $user->setLastChangePasswordDate(null);
        self::assertTrue($user->isForcePasswordChange());
    }

    public function testWhetherToForcePasswordChangeAfter6DaysIsTrue()
    {
        $user = new User();
        $user->setLastChangePasswordDate(new DateTime('-6 days'));
        self::assertTrue($user->isForcePasswordChange(5));
    }
}