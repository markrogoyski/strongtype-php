<?php

declare(strict_types=1);

namespace StrongType\Integer;

use StrongType\Exception\StrongTypeException;

class NegativeInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);
        $this->validateNegativeInt();
    }

    private function validateNegativeInt(): void
    {
        if ($this->value >= 0) {
            throw new StrongTypeException();
        }
    }
}
