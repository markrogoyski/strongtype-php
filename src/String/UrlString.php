<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

class UrlString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->validateUrlString();
    }

    private function validateUrlString(): void
    {
        if (\filter_var($this->value, \FILTER_VALIDATE_URL) === false) {
            throw new StrongTypeException("UrlString type must be a valid URL, got {$this->value}");
        }
    }
}
