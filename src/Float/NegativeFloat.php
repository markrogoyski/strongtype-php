<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Exception\StrongTypeException;

class NegativeFloat extends FloatingPoint
{
    public function __construct(float $value)
    {
        parent::__construct($value);
        $this->validateNegativeFloat();
    }

    private function validateNegativeFloat(): void
    {
        if ($this->value >= 0.0) {
            throw new StrongTypeException("NegativeFloat type must be < 0.0, got {$this->value}");
        }
    }
}
