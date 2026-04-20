<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ElementType;

/**
 * @extends NonemptyArray<array-key, callable>
 */
#[ElementType('callable')]
class ArrayOfCallables extends NonemptyArray
{
}
