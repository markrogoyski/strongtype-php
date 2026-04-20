<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, resource>
 */
#[ElementType('resource')]
class ArrayOfResources extends NonemptyArray
{
}
