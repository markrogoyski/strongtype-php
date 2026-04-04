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

        if (!\array_all($this->values, fn($v) => \is_resource($v))) {
            throw new StrongTypeException('ArrayOfResources type values must be resources');
        }
    }
}
