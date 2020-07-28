<?php

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
    private $type;

    /**
     * @var array
     */
    private $allowedTypes
        = [
            self::TYPE_LITERAL,
            self::TYPE_REGEXP
        ];

    /**
     * Route constructor.
     *
     * @param  string  $request
     * @param  string  $type
     */
    public function __construct(
        string $request,
        string $type
    ) {
        $this->request = $request;
        if (!in_array($type, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'route type should be one of: %s',
                    implode(', ', $this->allowedTypes)
                )
            );
        }
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isLiteral(): bool
    {
        return $this->type === self::TYPE_LITERAL;
    }

}
