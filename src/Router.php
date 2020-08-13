<?php

/*
 * This file is part of the Tiny package.
 *
 * (c) Alex Ermashev <alexermashevn@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tiny\Router;

use Tiny\Http\Request;

class Router
{

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Route|null
     */
    private ?Route $defaultRoute = null;

    /**
     * @var Route[]
     */
    private array $allRoutes = [];

    /**
     * @var array
     */
    private array $assembleRoutesMap = [];

    /**
     * @var string
     */
    private string $assembleParamDivider = '%';

    /**
     * @var array
     */
    private array $literalRoutesMap = [];

    /**
     * @var array
     */
    private array $regexpRoutesMap = [];

    /**
     * Router constructor.
     *
     * @param  Request  $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param  Route  $route
     *
     * @return $this
     */
    public function setDefaultRoute(Route $route): self
    {
        $this->defaultRoute = $route;

        return $this;
    }

    /**
     * @return Route
     */
    public function getDefaultRoute(): Route
    {
        return $this->defaultRoute;
    }

    /**
     * @param  string  $divider
     *
     * @return $this
     */
    public function setAssembleParamDivider(string $divider): self
    {
        $this->assembleParamDivider = $divider;

        return $this;
    }

    /**
     * @param  Route[]  $routes
     */
    public function registerRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->registerRoute($route);
        }
    }

    /**
     * @param  Route  $route
     */
    public function registerRoute(Route $route)
    {
        // we need to store all routes for mapping and assembling (even they are not used in the matching)
        $this->allRoutes[] = $route;
        $index = count($this->allRoutes) - 1;

        // for the matching we need only routes which support the current request's method (others should be skipped)
        // if there is a route without a method definition we consider it fits to all the methods and we also include it in the matching
        $isMatchableRoute = is_array($route->getActionList())
            ? in_array(
                $this->request->getMethod(), array_keys($route->getActionList())
            )
            : true;

        // register the map for assembling routes
        $this->assembleRoutesMap[$route->getController()][] = $index;

        if ($isMatchableRoute) {
            $route->isLiteral()
                ? $this->literalRoutesMap[$route->getRequest()] = $index
                : $this->regexpRoutesMap[] = $index;
        }
    }

    /**
     * @return Route
     */
    public function getMatchedRoute(): Route
    {
        $request = $this->request->getRequest();

        // find the request in the literal routes
        if (isset($this->literalRoutesMap[$request])) {
            return $this->initMatchedControllerAction(
                $this->allRoutes[$this->literalRoutesMap[$request]]
            );
        }

        // find the request in the regexp routes
        foreach ($this->regexpRoutesMap as $index) {
            /** @var Route $route */
            $route = $this->allRoutes[$index];
            $matches = [];

            // check if the request is matched by a regexp
            preg_match($route->getRequest(), $request, $matches);

            if ($matches) {
                $requestParams = [];

                // extract and fill the request's params
                foreach ($route->getRequestParams() as $param) {
                    if (isset($matches[$param])) {
                        $requestParams[$param] = $matches[$param];
                    }
                }

                // add the found request params to the request
                $this->request->setParams($requestParams);

                return $this->initMatchedControllerAction($route);
            }
        }

        // return a default route
        if ($this->defaultRoute) {
            return $this->initMatchedControllerAction($this->defaultRoute);
        }

        throw new Exception\InvalidArgumentException(
            sprintf(
                'The received request "%s"  cannot be matched with any existing routes',
                $request
            )
        );
    }

    /**
     * @param  string  $controller
     * @param  string  $action
     * @param  array   $params
     *
     * @return string
     */
    public function assembleRequest(
        string $controller,
        string $action,
        array $params = []
    ): string {
        // find a route
        if (isset($this->assembleRoutesMap[$controller])) {
            foreach ($this->assembleRoutesMap[$controller] as $index) {
                /** @var Route $route */
                $route = $this->allRoutes[$index];
                $isActionExist = is_array($route->getActionList())
                    ? in_array($action, $route->getActionList())
                    : $route->getActionList() === $action;

                if (!$isActionExist) {
                    continue;
                }

                // assemble the route
                if ($route->isLiteral()) {
                    return $route->getRequest();
                }

                $request = $route->getSpec();

                // process the request's params
                foreach ($params as $key => $value) {
                    // wrap the key using assemble param divider
                    $key = vsprintf('%s%s%s', [
                        $this->assembleParamDivider,
                        $key,
                        $this->assembleParamDivider
                    ]);
                    $request = str_replace($key, $value, $request);
                }

                return $request;
            }
        }

        throw new Exception\InvalidArgumentException(
            sprintf(
                'Cannot assemble request, there is no route with  "%s" controller and "%s" action',
                $controller,
                $action
            )
        );
    }

    /**
     * @param  Route  $route
     *
     * @return Route
     */
    private function initMatchedControllerAction(Route $route): Route
    {
        $matchedAction = is_array($route->getActionList())
            ? $route->getActionList()[$this->request->getMethod()]
            : $route->getActionList();

        $route->setMatchedAction($matchedAction);

        return $route;
    }

}
