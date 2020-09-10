<?php

/*
 * This file is part of the Tiny package.
 *
 * (c) Alex Ermashev <alexermashev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TinyTest\Router;

use PHPUnit\Framework\TestCase;
use Tiny\Router\Route;

class RouteTest extends TestCase
{

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

    public function testSettersAndGetters()
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
        $context = 'http';

        $instance = new Route(
            '',
            '',
            ''
        );

        $instance->setController($controller)
            ->setActionList($actionList)
            ->setMatchedAction($matchedAction)
            ->setRequest($request)
            ->setSpec($spec)
            ->setType(Route::TYPE_REGEXP)
            ->setRequestParams($requestParams)
            ->setContext($context);

        $this->assertEquals($request, $instance->getRequest());
        $this->assertEquals($controller, $instance->getController());
        $this->assertEquals($actionList, $instance->getActionList());
        $this->assertEquals($requestParams, $instance->getRequestParams());
        $this->assertEquals($spec, $instance->getSpec());
        $this->assertEquals($matchedAction, $instance->getMatchedAction());
        $this->assertEquals(Route::TYPE_REGEXP, $instance->getType());
        $this->assertEquals($context, $instance->getContext());
    }

}
