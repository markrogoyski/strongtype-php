<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Exception\StrongTypeException;

readonly class ClassString extends NonemptyString
{
    public function __construct(string $value)
    {
        parent::__construct($value);

        if (!\class_exists($this->value) && !\interface_exists($this->value) && !\enum_exists($this->value)) {
            throw new StrongTypeException("ClassString type must be a valid class, interface, or enum name, got {$this->value}");
        }
    }
}
