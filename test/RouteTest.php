<?php

namespace TinyTest\Router;

use PHPUnit\Framework\TestCase;
use Tiny\Router\Exception\InvalidArgumentException;
use Tiny\Router\Route;

class RouteTest extends TestCase
{

    public function testIncorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'route type should be one of: literal, regexp'
        );

        new Route(
            'test',
            'test2'
        );
    }

    public function testCorrectInitialization()
    {
        $instance = new Route(
            'test',
            Route::TYPE_LITERAL
        );
        $this->assertInstanceOf(Route::class, $instance);
    }

    public function testIsLiteral()
    {
        $instance = new Route(
            'test',
            Route::TYPE_LITERAL
        );
        $this->assertTrue($instance->isLiteral());
    }

}
