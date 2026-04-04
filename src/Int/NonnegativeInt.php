<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Exception\StrongTypeException;

readonly class NonnegativeInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value < 0) {
            throw new StrongTypeException("NonnegativeInt type must be >= 0, got {$this->value}");
        }
    }
}
