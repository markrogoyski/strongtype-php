<?php

declare(strict_types=1);

namespace StrongType\DateTime;

use StrongType\Exception\FormatException;

readonly class TimeString extends DateTime
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\preg_match('/\A([01]\d|2[0-3]):[0-5]\d:[0-5]\d\z/', $this->value)) {
            throw new FormatException("TimeString type must be a valid HH:MM:SS time, got {$this->value}");
        }
    }
}
