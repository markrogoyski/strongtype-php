<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfFloats extends NonemptyArray
{
    /**
     * @param float[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfFloats();
    }

    private function validateArrayOfFloats(): void
    {
        foreach ($this->values as $value) {
            if (!is_float($value)) {
                throw new StrongTypeException('ArrayOfFloats type values must be floats, got ' . print_r($value, true) . ' as a value');
            }
        }
    }

    public function current(): float
    {
        return \current($this->values);
    }
}
