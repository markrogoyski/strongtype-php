<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, string>
 */
#[ElementType('string')]
readonly class ArrayOfStrings extends NonemptyArray
{
}
