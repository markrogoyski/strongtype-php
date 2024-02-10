<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfStrings extends NonemptyArray
{
    /**
     * @param string[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfStrings();
    }

    private function validateArrayOfStrings(): void
    {
        foreach ($this->values as $value) {
            if (!is_string($value)) {
                throw new StrongTypeException('ArrayOfStrings type values must be strings, got ' . print_r($value, true) . ' as a value');
            }
        }
    }

    public function current(): string
    {
        return \current($this->values);
    }
}
