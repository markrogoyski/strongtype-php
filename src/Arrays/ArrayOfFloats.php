<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param float[] $values
 */
#[ElementType('float')]
class ArrayOfFloats extends NonemptyArray
{
    #[\Override]
    public function current(): float
    {
        /** @var float $current */
        $current = \current($this->values);
        return $current;
    }
}
