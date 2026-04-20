<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, string>
 */
#[ElementType('string')]
class ArrayOfStrings extends NonemptyArray
{
}
