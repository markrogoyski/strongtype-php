<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class DateTimeString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        try {
            new \DateTimeImmutable($this->value);
        } catch (\Exception) {
            throw new StrongTypeException("DateTimeString type must be a valid datetime string, got {$this->value}");
        }
    }
}
