<?php

declare(strict_types=1);

namespace StrongType\Bool;

use StrongType\Exception\StrongTypeException;

class FalseValue extends BoolType
{
    public function __construct(bool $value)
    {
        parent::__construct($value);
        $this->validateFalseValue();
    }

    private function validateFalseValue(): void
    {
        if ($this->value !== false) {
            throw new StrongTypeException("FalseValue type must be false, got true");
        }
    }
}
