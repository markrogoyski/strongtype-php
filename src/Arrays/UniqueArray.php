<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class UniqueArray extends NonemptyArray
{
    /**
     * @param mixed[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (\count($this->values) !== \count(\array_unique($this->values, \SORT_REGULAR))) {
            throw new StrongTypeException('UniqueArray type must not contain duplicate values');
        }
    }
}
