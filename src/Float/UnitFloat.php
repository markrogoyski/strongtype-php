<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Exception\StrongTypeException;

class UnitFloat extends FloatingPoint
{
    public function __construct(float $value)
    {
        parent::__construct($value);
        $this->validateUnitFloat();
    }

    private function validateUnitFloat(): void
    {
        if ($this->value < 0.0 || $this->value > 1.0) {
            throw new StrongTypeException("UnitFloat type must be between 0.0 and 1.0 inclusive, got {$this->value}");
        }
    }
}
