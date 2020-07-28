<?php

namespace TinyTest\Router;

use PHPUnit\Framework\TestCase;
use Tiny\Router\Router;

class RouterTest extends TestCase
{

    public function testTest()
    {
        $match = new Router();

        $this->assertEquals('a', 'a');
    }

}
