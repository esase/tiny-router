<?php

namespace Tiny\Router;

class Router
{

    /**
     * @var Route[]
     */
    protected $routes;

    public function registerRoute(Route $route)
    {
        $this->routes[] = $route;
    }

}
