<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class NonblankString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (\trim($this->value) === '') {
            throw new StrongTypeException("NonblankString type must not be blank, got '{$this->value}'");
        }
    }
}
