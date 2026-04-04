<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class LowercaseAlphaString extends AlphaString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\ctype_lower($this->value)) {
            throw new StrongTypeException("LowercaseAlphaString type must only contain lowercase characters, got {$this->value}");
        }
    }
}
