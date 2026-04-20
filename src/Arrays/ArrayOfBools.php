<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, bool>
 */
#[ElementType('bool')]
class ArrayOfBools extends NonemptyArray
{
}
