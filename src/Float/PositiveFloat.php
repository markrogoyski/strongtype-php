<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Exception\StrongTypeException;

class PositiveFloat extends FloatingPoint
{
    public function __construct(float $value)
    {
        parent::__construct($value);
        $this->validatePositiveFloat();
    }

    private function validatePositiveFloat(): void
    {
        if ($this->value <= 0.0) {
            throw new StrongTypeException("PositiveFloat type must be > 0.0, got {$this->value}");
        }
    }
}
