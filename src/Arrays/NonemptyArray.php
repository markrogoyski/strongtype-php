<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\Nonempty;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends ArrayType<TKey, TValue>
 */
#[Nonempty]
class NonemptyArray extends ArrayType
{
}
