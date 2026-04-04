<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class NumericString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\ctype_digit($this->value)) {
            throw new StrongTypeException("NumericString type must only contain numeric characters, got {$this->value}");
        }
    }
}
