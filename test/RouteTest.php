<?php

/*
 * This file is part of the Tiny package.
 *
 * (c) Alex Ermashev <alexermashevn@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            'Route type should be one of: literal, regexp'
        );

        new Route(
            '/test',
            'TestController',
            [],
            'test'
        );
    }

    public function testIsLiteralMethod()
    {
        $instance = new Route(
            '/test',
            'TestController',
            [],
            Route::TYPE_LITERAL
        );
        $this->assertTrue($instance->isLiteral());
    }

    public function testGetters()
    {
        $request = '|^/test/(?P<id>\d+)$|i';
        $controller = 'TestController';
        $actionList = [
            'GET' => 'test',
        ];
        $requestParams = [
            'id',
        ];
        $spec = '/test/%id%';
        $matchedAction = 'test';
        $instance = new Route(
            $request,
            $controller,
            $actionList,
            Route::TYPE_REGEXP,
            $requestParams,
            $spec
        );

        $instance->setMatchedAction($matchedAction);
        $this->assertEquals($request, $instance->getRequest());
        $this->assertEquals($controller, $instance->getController());
        $this->assertEquals($actionList, $instance->getActionList());
        $this->assertEquals($requestParams, $instance->getRequestParams());
        $this->assertEquals($spec, $instance->getSpec());
        $this->assertEquals($matchedAction, $instance->getMatchedAction());
    }

    public function testRegexRouteWithoutSpec()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The regexp route must be provided with `spec` for assembling requests'
        );

        new Route(
            '|^/test/(?P<id>\d+)$|i',
            'TestController',
            'test',
            Route::TYPE_REGEXP,
            []
        );
    }

}
