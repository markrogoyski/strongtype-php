<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class JsonString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\json_validate($this->value)) {
            throw new StrongTypeException("JsonString type must be valid JSON: " . \json_last_error_msg() . ", got {$this->value}");
        }
    }
}
