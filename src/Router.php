<?php

namespace Tiny\Router;

class Router
{

    /**
     * @var
     */
    private $request;

    /**
     * @var Route[]
     */
    private $literalRoutes = [];

    /**
     * @var Route[]
     */
    private $regexpRoutes = [];

    public function __construct(string $request)
    {
        $this->request = $request;
    }

    /**
     * @param Route $route
     */
    public function registerRoute(Route $route)
    {
        if ($route->isLiteral()) {
            $this->literalRoutes[] = $route;
            return;
        }
        $this->regexpRoutes[] = $route;
    }
}
