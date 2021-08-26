<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Exception\StrongTypeException;

class NonnegativeFloat extends FloatingPoint
{
    public function __construct(float $value)
    {
        parent::__construct($value);
        $this->validateNonegativeFloat();
    }

    private function validateNonegativeFloat(): void
    {
        if ($this->value < 0.0) {
            throw new StrongTypeException();
        }
    }
}
