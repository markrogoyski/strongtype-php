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
        try {
            /** @psalm-suppress UnusedFunctionCall - called for validation side-effect */
            \json_decode($this->value, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new StrongTypeException("JsonString type must be valid JSON, got {$this->value}");
        }
    }
}
