<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class SlugString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $this->value)) {
            throw new StrongTypeException("SlugString type must be a valid URL slug, got {$this->value}");
        }
    }
}
