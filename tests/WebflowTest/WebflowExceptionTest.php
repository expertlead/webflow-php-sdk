<?php

namespace WebflowTest;

use PHPUnit\Framework\TestCase;
use Webflow\WebflowException;

final class WebflowExceptionTest extends TestCase
{
    public function testCanInstantiateWebflowException()
    {
        $this->assertInstanceOf(
            WebflowException::class,
            new WebflowException('test')
        );
    }
}
