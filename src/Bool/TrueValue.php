<?php

declare(strict_types=1);

namespace StrongType\Bool;

use StrongType\Exception\StrongTypeException;

class TrueValue extends BoolType
{
    public function __construct(bool $value)
    {
        parent::__construct($value);
        $this->validateTrueValue();
    }

    private function validateTrueValue(): void
    {
        if ($this->value !== true) {
            throw new StrongTypeException("TrueValue type must be true, got false");
        }
    }
}
