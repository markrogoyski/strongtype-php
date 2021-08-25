<?php

declare(strict_types=1);

namespace StrongType\Integer;

use StrongType\Exception\StrongTypeException;

class NonpositiveInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);
        $this->validateNonpositiveInt();
    }

    private function validateNonpositiveInt(): void
    {
        if ($this->value > 0) {
            throw new StrongTypeException();
        }
    }
}
