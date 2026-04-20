<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, iterable<mixed>>
 */
#[ElementType('iterable')]
class ArrayOfIterables extends NonemptyArray
{
}
