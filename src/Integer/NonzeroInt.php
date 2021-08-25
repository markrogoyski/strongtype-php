<?php

declare(strict_types=1);

namespace StrongType\Integer;

use StrongType\Exception\StrongTypeException;

class NonzeroInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);
        $this->validateNonzeroInt();
    }

    private function validateNonzeroInt(): void
    {
        if ($this->value === 0) {
            throw new StrongTypeException();
        }
    }
}
