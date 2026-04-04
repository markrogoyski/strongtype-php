<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class NonemptyArray extends ArrayType
{
    /**
     * @param mixed[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (\count($this->values) === 0) {
            throw new StrongTypeException('NonemptyArray type must not be empty, got ' . \print_r($this->values, true));
        }
    }
}
