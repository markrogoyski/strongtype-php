<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class EmptyString extends StringType
{
    public function __construct(string $value = '')
    {
        parent::__construct($value);

        if (\strlen($this->value) > 0) {
            throw new StrongTypeException("EmptyString type must be empty, got {$this->value}");
        }
    }
}
