<?php

/*
 * This file is part of the Tiny package.
 *
 * (c) Alex Ermashev <alexermashev@gmail.com>
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
    private string $request;

    /**
     * @var string
     */
    private string $controller;

    /**
     * @var array|string
     */
    private $actionList;

    /**
     * @var string
     */
    private string $type;

    /**
     * @var array
     */
    private array $requestParams = [];

    /**
     * @var string
     */
    private string $spec;

    /**
     * @var string
     */
    private string $context;

    /**
     * @var string|null
     */
    private ?string $matchedAction;

    /**
     * Route constructor.
     *
     * @param string       $request
     * @param string       $controller
     * @param string|array $actionList
     * @param string       $type
     * @param array        $requestParams
     * @param string       $spec
     * @param string       $context
     */
    public function __construct(
        string $request,
        string $controller,
        $actionList,
        string $type = self::TYPE_LITERAL,
        array $requestParams = [],
        string $spec = '',
        string $context = ''
    ) {
        $this->request = $request;
        $this->type = $type;
        $this->controller = $controller;
        $this->actionList = $actionList;
        $this->requestParams = $requestParams;
        $this->spec = $spec;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    /**
     * @param string $request
     *
     * @return $this
     */
    public function setRequest(string $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return $this
     */
    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getActionList()
    {
        return $this->actionList;
    }

    /**
     * @param array|string $actionList
     *
     * @return $this
     */
    public function setActionList($actionList): self
    {
        $this->actionList = $actionList;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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
     * @param array $requestParams
     *
     * @return $this
     */
    public function setRequestParams(array $requestParams): self
    {
        $this->requestParams = $requestParams;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpec(): string
    {
        return $this->spec;
    }

    /**
     * @param string $spec
     *
     * @return $this
     */
    public function setSpec(string $spec): self
    {
        $this->spec = $spec;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     *
     * @return $this
     */
    public function setContext(string $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMatchedAction()
    {
        return $this->matchedAction;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setMatchedAction(string $action): self
    {
        $this->matchedAction = $action;

        return $this;
    }

}
