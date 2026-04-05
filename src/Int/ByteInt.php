<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Exception\StrongTypeException;

readonly class ByteInt extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value < 0 || $this->value > 255) {
            throw new StrongTypeException("ByteInt type must be between 0 and 255, got {$this->value}");
        }
    }
}
