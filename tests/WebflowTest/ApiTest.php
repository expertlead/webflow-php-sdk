<?php

namespace WebflowTest;

use PHPUnit\Framework\TestCase;
use Webflow\Api;
use Webflow\WebflowException;

final class ApiTest extends TestCase
{
    public function testCanInstantiateApi()
    {
        $this->assertInstanceOf(
            Api::class,
            new Api('token')
        );
    }

    public function testNegativeEmptyToken()
    {
        $this->expectException(WebflowException::class);
        new Api('');
    }
}
