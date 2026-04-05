<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class SemverString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(-[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*)?(\+[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*)?$/', $this->value)) {
            throw new StrongTypeException("SemverString type must be a valid semantic version, got {$this->value}");
        }
    }
}
