<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Exception\StrongTypeException;

readonly class NegativeFloat extends FloatingPoint
{
    public function __construct(float $value)
    {
        parent::__construct($value);

        if ($this->value >= 0.0) {
            throw new StrongTypeException("NegativeFloat type must be < 0.0, got {$this->value}");
        }
    }
}
