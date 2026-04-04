<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class BinaryString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\preg_match('/^[01]+$/', $this->value)) {
            throw new StrongTypeException("BinaryString type must only contain binary digits, got {$this->value}");
        }
    }
}
