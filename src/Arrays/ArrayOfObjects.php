<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, object>
 */
#[ElementType('object')]
readonly class ArrayOfObjects extends NonemptyArray
{
}
