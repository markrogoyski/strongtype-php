<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfBools extends NonemptyArray
{
    /**
     * @param bool[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfBools();
    }

    private function validateArrayOfBools(): void
    {
        foreach ($this->values as $value) {
            if (!is_bool($value)) {
                throw new StrongTypeException('ArrayOfBools type values must be bools, got ' . print_r($value, true) . ' as a value');
            }
        }
    }

    public function current(): bool
    {
        return \current($this->values);
    }
}
