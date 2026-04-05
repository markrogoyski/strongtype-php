<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param bool[] $values
 */
#[ElementType('bool')]
class ArrayOfBools extends NonemptyArray
{
    #[\Override]
    public function current(): bool
    {
        /** @var bool $current */
        $current = \current($this->values);
        return $current;
    }
}
