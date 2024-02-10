<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfArrays extends NonemptyArray
{
    /**
     * @param array[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfArrays();
    }

    private function validateArrayOfArrays(): void
    {
        foreach ($this->values as $value) {
            if (!is_array($value)) {
                throw new StrongTypeException('ArrayOfArrays type values must be arrays, got ' . print_r($value, true) . ' as a value');
            }
        }
    }

    /**
     * @return array<mixed>
     */
    public function current(): array
    {
        return \current($this->values);
    }
}
