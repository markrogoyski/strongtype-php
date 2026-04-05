<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Exception\StrongTypeException;

readonly class PercentInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value < 0 || $this->value > 100) {
            throw new StrongTypeException("PercentInt type must be between 0 and 100 inclusive, got {$this->value}");
        }
    }
}
