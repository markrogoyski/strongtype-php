<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

class Base64String extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->validateBase64String();
    }

    private function validateBase64String(): void
    {
        if (!preg_match('/^[A-Za-z0-9+\/]*={0,2}$/', $this->value)) {
            throw new StrongTypeException("Base64String type must be valid base64, got {$this->value}");
        }
    }
}
