<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Util\Stringify;

class ArrayOfInts extends NonemptyArray
{
    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        $this->validateArrayOfInts();
    }

    private function validateArrayOfInts(): void
    {
        foreach ($this->values as $value) {
            if (!\is_int($value)) {
                throw new StrongTypeException('ArrayOfInts type values must be ints, got ' . Stringify::value($value) . ' as a value');
            }
        }
    }

    public function current(): int
    {
        /** @var int $current */
        $current = \current($this->values);
        return $current;
    }
}
