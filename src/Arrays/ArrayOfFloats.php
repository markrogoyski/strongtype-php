<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, float>
 */
#[ElementType('float')]
readonly class ArrayOfFloats extends NonemptyArray
{
}
