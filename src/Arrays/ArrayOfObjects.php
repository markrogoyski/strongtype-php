<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param object[] $values
 */
#[ElementType('object')]
class ArrayOfObjects extends NonemptyArray
{
    #[\Override]
    public function current(): object
    {
        /** @var object $current */
        $current = \current($this->values);
        return $current;
    }
}
