<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class EmailString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (\filter_var($this->value, \FILTER_VALIDATE_EMAIL) === false) {
            throw new StrongTypeException("EmailString type must be a valid email address, got {$this->value}");
        }
    }
}
