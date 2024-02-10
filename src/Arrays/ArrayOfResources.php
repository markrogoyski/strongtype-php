<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfResources extends NonemptyArray
{
    /**
     * @param resource[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfResources();
    }

    private function validateArrayOfResources(): void
    {
        foreach ($this->values as $value) {
            if (!is_resource($value)) {
                throw new StrongTypeException('ArrayOfResources type values must be resources, got ' . print_r($value, true) . ' as a value');
            }
        }
    }
}
