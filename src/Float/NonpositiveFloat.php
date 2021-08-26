<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Exception\StrongTypeException;

class NonpositiveFloat extends FloatingPoint
{
    public function __construct(float $value)
    {
        parent::__construct($value);
        $this->validateNonpositiveFloat();
    }

    private function validateNonpositiveFloat(): void
    {
        if ($this->value > 0.0) {
            throw new StrongTypeException("NonpositiveFloat type must be <= 0.0, got {$this->value}");
        }
    }
}
