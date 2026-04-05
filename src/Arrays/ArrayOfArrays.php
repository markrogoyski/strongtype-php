<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param array<mixed>[] $values
 */
#[ElementType('array')]
class ArrayOfArrays extends NonemptyArray
{
    /**
     * @return array<mixed>
     */
    #[\Override]
    public function current(): array
    {
        /** @var array<mixed> $current */
        $current = \current($this->values);
        return $current;
    }
}
