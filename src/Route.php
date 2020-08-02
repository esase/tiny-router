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

class Route
{

    const TYPE_LITERAL = 'literal';

    const TYPE_REGEXP = 'regexp';

    /**
     * @var string
     */
    private $request;

    /**
     * @var string
     */
    private $controller;

    /**
     * @var array|string
     */
    private $actionList;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $requestParams = [];

    /**
     * @var
     */
    private $spec;

    /**
     * @var string
     */
    private $matchedAction;

    /**
     * @var array
     */
    private $allowedTypes
        = [
            self::TYPE_LITERAL,
            self::TYPE_REGEXP,
        ];

    /**
     * Route constructor.
     *
     * @param  string        $request
     * @param  string        $controller
     * @param  string|array  $actionList
     * @param  string        $type
     * @param  array         $requestParams
     * @param  string        $spec
     */
    public function __construct(
        string $request,
        string $controller,
        $actionList,
        string $type = self::TYPE_LITERAL,
        array $requestParams = [],
        string $spec = ''
    ) {
        $this->request = $request;
        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Route type should be one of: %s',
                    implode(', ', $this->allowedTypes)
                )
            );
        }

        $this->type = $type;
        $this->controller = $controller;
        $this->actionList = $actionList;
        $this->requestParams = $requestParams;
        $this->spec = $spec;

        if (!$this->isLiteral() && !$this->spec) {
            throw new Exception\InvalidArgumentException(
                'The regexp route must be provided with `spec` for assembling requests'
            );
        }
    }

    /**
     * @return string
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return array|string
     */
    public function getActionList()
    {
        return $this->actionList;
    }

    /**
     * @return bool
     */
    public function isLiteral(): bool
    {
        return $this->type === self::TYPE_LITERAL;
    }

    /**
     * @return array
     */
    public function getRequestParams(): array
    {
        return $this->requestParams;
    }

    /**
     * @return string
     */
    public function getSpec(): string
    {
        return $this->spec;
    }

    /**
     * @return string
     */
    public function getMatchedAction(): string
    {
        return $this->matchedAction;
    }

    /**
     * @param  string  $action
     */
    public function setMatchedAction(string $action)
    {
        $this->matchedAction = $action;
    }

}
