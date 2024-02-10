<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfCallables extends NonemptyArray
{
    /**
     * @param callable[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfCallables();
    }

    private function validateArrayOfCallables(): void
    {
        foreach ($this->values as $value) {
            if (!is_callable($value)) {
                throw new StrongTypeException('ArrayOfCallables type values must be callables, got ' . print_r($value, true) . ' as a value');
            }
        }
    }

    public function current(): callable
    {
        return \current($this->values);
    }
}
