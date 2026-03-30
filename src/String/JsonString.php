<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

class JsonString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->validateJsonString();
    }

    private function validateJsonString(): void
    {
        json_decode($this->value);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            throw new StrongTypeException("JsonString type must be valid JSON, got {$this->value}");
        }
    }
}
