<?php

namespace Inmobalia\OAuth2\Client\Provider;

use Mockery as m;
use PHPUnit\Framework\TestCase;

class InmobaliaCrmResourceOwnerTest extends TestCase
{
    public function testEmailIsNull(): void
    {
        $user = new InmobaliaCrmResourceOwner();

        $url = $user->getEmail();

        $this->assertNull($url);
    }
}
