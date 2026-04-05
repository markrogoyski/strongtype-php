<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param callable[] $values
 */
#[ElementType('callable')]
class ArrayOfCallables extends NonemptyArray
{
    #[\Override]
    public function current(): callable
    {
        /** @var callable $current */
        $current = \current($this->values);
        return $current;
    }
}
