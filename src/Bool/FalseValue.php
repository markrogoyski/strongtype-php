<?php

declare(strict_types=1);

namespace StrongType\Bool;

readonly class FalseValue extends BoolType
{
    public function __construct(false $value)
    {
        parent::__construct($value);
    }
}
