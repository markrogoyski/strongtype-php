<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class EmptyArray extends ArrayType
{
    /**
     * @param null[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateEmptyArray();
    }

    private function validateEmptyArray(): void
    {
        if (count($this->values) !== 0) {
            throw new StrongTypeException('EmptyArray type must be empty, got ' . print_r($this->values, true));
        }
    }
}
