<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Exception\StrongTypeException;

class NonnegativeInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);
        $this->validateNonnegativeInt();
    }

    private function validateNonnegativeInt(): void
    {
        if ($this->value < 0) {
            throw new StrongTypeException();
        }
    }
}
