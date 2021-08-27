<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

class EmptyString extends StringType
{
    public function __construct(string $value = '')
    {
        parent::__construct($value);
        $this->validateEmptyString();
    }

    private function validateEmptyString(): void
    {
        if (strlen($this->value) > 0) {
            throw new StrongTypeException("EmptyString type must be empty, got {$this->value}");
        }
    }
}
