<?php

namespace Tiny\Router\Matcher;

interface MatcherInterface {

    /**
     * @return bool
     */
    public function isMatch(): bool;
}