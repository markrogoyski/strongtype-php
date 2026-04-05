<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param iterable<mixed>[] $values
 */
#[ElementType('iterable')]
class ArrayOfIterables extends NonemptyArray
{
    /**
     * @return iterable<mixed>
     */
    #[\Override]
    public function current(): iterable
    {
        /** @var iterable<mixed> $current */
        $current = \current($this->values);
        return $current;
    }
}
