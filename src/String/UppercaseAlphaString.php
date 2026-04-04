<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class UppercaseAlphaString extends AlphaString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\ctype_upper($this->value)) {
            throw new StrongTypeException("UppercaseAlphaString type must only contain uppercase characters, got {$this->value}");
        }
    }
}
