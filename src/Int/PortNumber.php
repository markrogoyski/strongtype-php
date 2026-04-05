<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Exception\StrongTypeException;

readonly class PortNumber extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value < 1 || $this->value > 65535) {
            throw new StrongTypeException("PortNumber type must be between 1 and 65535, got {$this->value}");
        }
    }
}
