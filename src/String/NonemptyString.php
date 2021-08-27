<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

class NonemptyString extends StringType
{
    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->validateNonemptyString();
    }

    private function validateNonemptyString(): void
    {
        if (strlen($this->value) === 0) {
            throw new StrongTypeException("NonemptyString type must not be empty, got {$this->value}");
        }
    }
}
