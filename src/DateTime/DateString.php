<?php

declare(strict_types=1);

namespace StrongType\DateTime;

use StrongType\Exception\StrongTypeException;

readonly class DateString extends DateTime
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->value)) {
            throw new StrongTypeException("DateString type must be a valid YYYY-MM-DD date, got {$this->value}");
        }

        [$year, $month, $day] = \array_map('intval', \explode('-', $this->value));
        if (!\checkdate($month, $day, $year)) {
            throw new StrongTypeException("DateString type must be a valid YYYY-MM-DD date, got {$this->value}");
        }
    }
}
