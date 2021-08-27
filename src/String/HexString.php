<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

class HexString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->validateHexString();
    }

    private function validateHexString(): void
    {
        if (!ctype_xdigit($this->value)) {
            throw new StrongTypeException("HexString type must only contain hexadecimal digits, got {$this->value}");
        }
    }
}
