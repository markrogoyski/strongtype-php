<?php

declare(strict_types=1);

namespace StrongType\Bool;

readonly class TrueValue extends BoolType
{
    public function __construct(true $value)
    {
        parent::__construct($value);
    }
}
