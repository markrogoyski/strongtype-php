<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class FixedSizeArray extends ArrayType
{
    /**
     * @param mixed[] $values
     */
    public function __construct(array $values, private(set) int $size)
    {
        parent::__construct($values);

        if (\count($this->values) !== $this->size) {
            throw new StrongTypeException("FixedSizeArray type must have exactly {$this->size} elements, got " . \count($this->values));
        }
    }
}
