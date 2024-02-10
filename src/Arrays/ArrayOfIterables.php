<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfIterables extends NonemptyArray
{
    /**
     * @param iterable[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfIterables();
    }

    private function validateArrayOfIterables(): void
    {
        foreach ($this->values as $value) {
            if (!is_iterable($value)) {
                throw new StrongTypeException('ArrayOfIterables type values must be iterables, got ' . print_r($value, true) . ' as a value');
            }
        }
    }

    /**
     * @return iterable<mixed>
     */
    public function current(): iterable
    {
        return \current($this->values);
    }
}
