<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Exception\StrongTypeException;

readonly class EvenInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value % 2 !== 0) {
            throw new StrongTypeException("EvenInt type must be even, got {$this->value}");
        }
    }
}
