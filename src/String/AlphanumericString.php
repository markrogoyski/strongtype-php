<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

class AlphanumericString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->validateAlphanumericString();
    }

    private function validateAlphanumericString(): void
    {
        if (!ctype_alnum($this->value)) {
            throw new StrongTypeException("AlphanumericString type must only contain alphanumeric characters, got {$this->value}");
        }
    }
}
