<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

class BinaryString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->validateBinaryString();
    }

    private function validateBinaryString(): void
    {
        if (!preg_match('/^[01]+$/', $this->value)) {
            throw new StrongTypeException("BinaryString type must only contain binary digits, got {$this->value}");
        }
    }
}
