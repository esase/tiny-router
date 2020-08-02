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
use ReflectionClass;
use ReflectionException;
use Tiny\Http\Request;
use Tiny\Router\Exception\InvalidArgumentException;
use Tiny\Router\Route;
use Tiny\Router\Router;

class RouterTest extends TestCase
{

    public function testGetDefaultRouteMethod()
    {
        $routeMock = $this->createMock(
            Route::class
        );
        $router = new Router(
            $this->createStub(
                Request::class
            )
        );
        $router->setDefaultRoute($routeMock);

        $this->assertEquals($routeMock, $router->getDefaultRoute());
    }

    public function testGetMatchedRouteMethodUsingEmptyRouteList()
    {
        $request = '/test';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The received request "%s"  cannot be matched with any existing routes',
                $request
            )
        );

        /** @var  Request $requestStub */
        $requestStub = $this->createStub(Request::class);
        $requestStub
            ->method('getMethod')
            ->willReturn('GET');
        $requestStub
            ->method('getRequest')
            ->willReturn($request);

        // init the router
        $router = new Router($requestStub);
        $router->getMatchedRoute();
    }

    /**
     * @dataProvider routesProvider
     *
     * @param  string  $requestMethod
     * @param  string  $request
     * @param  array   $routes
     * @param  array   $expect
     * @param  Route   $defaultRoute
     */
    public function testGetMatchedRouteMethod(
        string $requestMethod,
        string $request,
        array $routes,
        array $expect,
        Route $defaultRoute = null
    ) {
        // init the request object
        /** @var  Request $requestStub */
        $requestStub = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(
                [ // don't mock these methods
                  'setParams',
                  'getParams',
                ]
            )
            ->getMock();
        $requestStub
            ->method('getMethod')
            ->willReturn($requestMethod);
        $requestStub
            ->method('getRequest')
            ->willReturn($request);

        // init the router
        $router = new Router($requestStub);
        $router->registerRoutes($routes);

        if ($defaultRoute) {
            $router->setDefaultRoute($defaultRoute);
        }

        list($expectedController, $expectedAction, $expectedParams) = $expect;

        $matchedRoute = $router->getMatchedRoute();
        $this->assertEquals(
            $expectedController, $matchedRoute->getController()
        );
        $this->assertEquals($expectedAction, $matchedRoute->getMatchedAction());

        if ($expectedParams) {
            $this->assertEquals($expectedParams, $requestStub->getParams());
        }
    }

    /**
     * @throws ReflectionException
     */
    public function testRegisterRouteMethod()
    {
        // build the request
        $requestMock = $this->createMock(
            Request::class
        );
        $requestMock->expects($this->exactly(3))
            ->method('getMethod')
            ->willReturn('GET');

        // build the "GET" literal route
        $literalGetRouteMock = $this->createMock(
            Route::class
        );
        $literalGetRouteMock->expects($this->exactly(2))
            ->method('getActionList')
            ->willReturn(
                [
                    'GET' => 'test',
                ]
            );
        $literalGetRouteMock->expects($this->once())
            ->method('getController')
            ->willReturn('TestLiteralController');
        $literalGetRouteMock->expects($this->once())
            ->method('isLiteral')
            ->willReturn(true);
        $literalGetRouteMock->expects($this->once())
            ->method('getRequest')
            ->willReturn('/test');

        // build the "REGEXP" route  (we don't specify the method, because it should be matched with any methods)
        $regexpRouteMock = $this->createMock(
            Route::class
        );
        $regexpRouteMock->expects($this->once())
            ->method('getActionList')
            ->willReturn(
                '/^test$/i'
            ); // matched with any: POST, GET, CONSOLE, etc
        $regexpRouteMock->expects($this->once())
            ->method('getController')
            ->willReturn('TestRegexpController');
        $regexpRouteMock->expects($this->once())
            ->method('isLiteral')
            ->willReturn(false);

        // build the "POST" literal route
        $literalPostRouteMock = $this->createMock(
            Route::class
        );
        $literalPostRouteMock->expects($this->exactly(2))
            ->method('getActionList')
            ->willReturn(
                [
                    'POST' => 'test',
                ]
            );
        $literalPostRouteMock->expects($this->once())
            ->method('getController')
            ->willReturn('TestLiteralController');

        // build the "POST" regexp route
        $regexpPostRouteMock = $this->createMock(
            Route::class
        );
        $regexpPostRouteMock->expects($this->exactly(2))
            ->method('getActionList')
            ->willReturn(
                [
                    'POST' => 'testRegexp',
                ]
            );
        $regexpPostRouteMock->expects($this->once())
            ->method('getController')
            ->willReturn('TestRegexpController');

        $router = new Router($requestMock);
        $router->registerRoute($literalGetRouteMock);
        $router->registerRoute($regexpRouteMock);
        $router->registerRoute($literalPostRouteMock);
        $router->registerRoute($regexpPostRouteMock);

        // check the built structures
        $reflection = new ReflectionClass($router);
        $reflectionAllRoutes = $reflection->getProperty('allRoutes');
        $reflectionAllRoutes->setAccessible(true);

        // check the all routes
        $this->assertEquals(
            [
                $literalGetRouteMock,
                $regexpRouteMock,
                $literalPostRouteMock,
                $regexpPostRouteMock,
            ], $reflectionAllRoutes->getValue($router)
        );

        $reflectionLiteralRoutesMap = $reflection->getProperty(
            'literalRoutesMap'
        );
        $reflectionLiteralRoutesMap->setAccessible(true);

        // check the literal map
        $this->assertEquals(
            [
                '/test' => 0,
            ], $reflectionLiteralRoutesMap->getValue($router)
        );

        $reflectionRegexpRoutesMap = $reflection->getProperty(
            'regexpRoutesMap'
        );
        $reflectionRegexpRoutesMap->setAccessible(true);

        // check the assemble map
        $this->assertEquals(
            [
                1,
            ], $reflectionRegexpRoutesMap->getValue($router)
        );

        $reflectionAssembleRoutesMap = $reflection->getProperty(
            'assembleRoutesMap'
        );
        $reflectionAssembleRoutesMap->setAccessible(true);

        $this->assertEquals(
            [
                'TestLiteralController' => [0, 2],
                'TestRegexpController'  => [1, 3],
            ], $reflectionAssembleRoutesMap->getValue($router)
        );
    }

    /**
     * @dataProvider assembleRequestProvider
     *
     * @param  array   $routes
     * @param  string  $controller
     * @param  string  $action
     * @param  string  $expectedRequest
     * @param  array   $params
     */
    public function testAssembleRequestMethod(
        array $routes,
        string $controller,
        string $action,
        string $expectedRequest,
        array $params = []
    ) {
        // init the router
        $router = new Router(
            $this->createStub(Request::class)
        );
        $router->registerRoutes($routes);
        $router->setAssembleParamDivider('__');
        $this->assertEquals($expectedRequest, $router->assembleRequest(
            $controller,
            $action,
            $params
        ));
    }

    public function testAssembleRequestMethodUsingEmptyRouteList()
    {
        $controller = 'TestController';
        $action = 'test';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                sprintf(
                    'Cannot assemble request, there is no route with  "%s" controller and "%s" action',
                    $controller,
                    $action
                )
            )
        );

        // init the router
        $router = new Router(
            $this->createStub(Request::class)
        );

        $router->assembleRequest(
            $controller,
            $action
        );
    }

    /**
     * @return array
     */
    public function assembleRequestProvider(): array
    {
        return [
            [
                [
                    new Route(
                        '/',
                        'HomeController',
                        'index'
                    ),
                    new Route(
                        '/users',
                        'UserController',
                        'list'
                    ),
                    new Route(
                        '|^/users/(?P<id>\d+)$|i',
                        'UserController',
                        [
                            'GET' => 'view',
                            'DELETE' => 'delete',
                        ],
                        'regexp',
                        ['id'],
                        '/users/__id__'
                    ),
                ],
                'UserController',
                'view',
                '/users/100',
                [
                    'id' => 100
                ]
            ],
            [
                [
                    new Route(
                        '/users',
                        'HomeController',
                        'list'
                    ),
                    new Route(
                        '/',
                        'HomeController',
                        'index'
                    )
                ],
                'HomeController',
                'index',
                '/'
            ]
        ];
    }

    /**
     * @return array
     */
    public function routesProvider(): array
    {
        return [
            [
                'GET',
                '/users',
                [],
                [
                    'DefaultController',
                    'index',
                ],
                new Route(
                    '',
                    'DefaultController',
                    'index'
                ),
            ],
            [
                'CONSOLE',
                'users import',
                [
                    new Route(
                        'users import',
                        'UserController',
                        [
                            'CONSOLE' => 'import',
                        ]
                    ),
                    new Route(
                        'users export',
                        'UserController',
                        'export'
                    ),
                ],
                [
                    'UserController',
                    'import',
                ],
            ],
            [
                'CONSOLE',
                'users delete tester --force',
                [
                    new Route(
                        '/^users delete (?P<name>\w+)(\s(?<force>(--force|-f)))?$/i',
                        'UserController',
                        'delete',
                        'regexp',
                        ['name', 'force'],
                        'users delete %name%'
                    ),
                ],
                [
                    'UserController',
                    'delete',
                    ['name' => 'tester', 'force' => '--force'],
                ],
            ],
            [
                'CONSOLE',
                'users delete tester',
                [
                    new Route(
                        '|^users delete (?P<name>\w+)$|i',
                        'UserController',
                        'delete',
                        'regexp',
                        ['name'],
                        'users delete %name%'
                    ),
                    new Route(
                        'users import',
                        'UserController',
                        'import'
                    ),
                ],
                [
                    'UserController',
                    'delete',
                    ['name' => 'tester'],
                ],
            ],
            [
                'GET',
                '/users/100',
                [
                    new Route(
                        '|^/users/(?P<id>\d+)$|i',
                        'UserController',
                        [
                            'GET' => 'view',
                        ],
                        'regexp',
                        ['id'],
                        '/users/%id%'
                    ),
                    new Route(
                        '/users',
                        'UserController',
                        [
                            'GET'  => 'list',
                            'POST' => 'create',
                        ]
                    ),
                    new Route(
                        '/books',
                        'BookController',
                        [
                            'GET'  => 'list',
                            'POST' => 'create',
                        ]
                    ),
                ],
                [
                    'UserController',
                    'view',
                    ['id' => 100],
                ],
            ],
            [
                'GET',
                '/users',
                [
                    new Route(
                        '/users',
                        'UserController',
                        [
                            'GET'  => 'list',
                            'POST' => 'create',
                        ]
                    ),
                    new Route(
                        '/books',
                        'BookController',
                        [
                            'GET'  => 'list',
                            'POST' => 'create',
                        ]
                    ),
                ],
                [
                    'UserController',
                    'list',
                ],
            ],
            [
                'POST',
                '/',
                [
                    new Route(
                        '/',
                        'HomeController',
                        'index'
                    ),
                    new Route(
                        '/books',
                        'BookController',
                        [
                            'GET'  => 'list',
                            'POST' => 'create',
                        ]
                    ),
                ],
                [
                    'HomeController',
                    'index',
                ],
            ],
        ];
    }

}
