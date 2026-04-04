<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class AlphaString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\ctype_alpha($this->value)) {
            throw new StrongTypeException("AlphaString type must only contain alphabetic characters, got {$this->value}");
        }
    }
}
