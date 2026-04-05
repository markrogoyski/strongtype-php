<?php

declare(strict_types=1);

namespace StrongType\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;

readonly class Timestamp extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value < 0) {
            throw new StrongTypeException("Timestamp type must be >= 0, got {$this->value}");
        }
    }
}
