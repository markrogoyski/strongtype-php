<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @param resource[] $values
 */
#[ElementType('resource')]
class ArrayOfResources extends NonemptyArray
{
}
