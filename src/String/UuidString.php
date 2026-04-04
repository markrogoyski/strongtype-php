<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class UuidString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->value)) {
            throw new StrongTypeException("UuidString type must be a valid UUID, got {$this->value}");
        }
    }
}
