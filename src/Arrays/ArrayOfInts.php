<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

class ArrayOfInts extends NonemptyArray
{
    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!\array_all($this->values, fn($v) => \is_int($v))) {
            throw new StrongTypeException('ArrayOfInts type values must be ints');
        }
    }

    #[\Override]
    public function current(): int
    {
        /** @var int $current */
        $current = \current($this->values);
        return $current;
    }
}
