<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\Unique;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends NonemptyArray<TKey, TValue>
 */
#[Unique]
class UniqueArray extends NonemptyArray
{
}
