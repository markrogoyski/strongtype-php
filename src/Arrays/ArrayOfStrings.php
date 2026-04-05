<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param string[] $values
 */
#[ElementType('string')]
class ArrayOfStrings extends NonemptyArray
{
    #[\Override]
    public function current(): string
    {
        /** @var string $current */
        $current = \current($this->values);
        return $current;
    }
}
