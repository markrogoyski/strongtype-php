<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param int[] $values
 */
#[ElementType('int')]
class ArrayOfInts extends NonemptyArray
{
    #[\Override]
    public function current(): int
    {
        /** @var int $current */
        $current = \current($this->values);
        return $current;
    }
}
