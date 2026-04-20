<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, array<mixed>>
 */
#[ElementType('array')]
class ArrayOfArrays extends NonemptyArray
{
}
