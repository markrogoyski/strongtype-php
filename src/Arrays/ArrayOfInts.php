<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfInts extends NonemptyArray
{
    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfInts();
    }

    private function validateArrayOfInts(): void
    {
        foreach ($this->values as $value) {
            if (!is_int($value)) {
                throw new StrongTypeException('ArrayOfInts type values must be ints, got ' . print_r($value, true) . ' as a value');
            }
        }
    }

    public function current(): int
    {
        return \current($this->values);
    }
}
