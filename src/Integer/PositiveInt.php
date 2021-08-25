<?php

declare(strict_types=1);

namespace StrongType\Integer;

use StrongType\Exception\StrongTypeException;

class PositiveInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);
        $this->validatePositiveInt();
    }

    private function validatePositiveInt(): void
    {
        if ($this->value <= 0) {
            throw new StrongTypeException();
        }
    }
}
