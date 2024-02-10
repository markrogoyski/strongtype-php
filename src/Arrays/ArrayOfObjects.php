<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfObjects extends NonemptyArray
{
    /**
     * @param object[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfObjects();
    }

    private function validateArrayOfObjects(): void
    {
        foreach ($this->values as $value) {
            if (!is_float($value)) {
                throw new StrongTypeException('ArrayOfObjects type values must be objects, got ' . print_r($value, true) . ' as a value');
            }
        }
    }

    public function current(): object
    {
        return \current($this->values);
    }
}
