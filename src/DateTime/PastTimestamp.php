<?php

declare(strict_types=1);

namespace StrongType\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;

readonly class PastTimestamp extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->value >= \time()) {
            throw new StrongTypeException("PastTimestamp type must be in the past, got {$this->value}");
        }
    }
}
