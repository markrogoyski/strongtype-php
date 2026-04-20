<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\IsAssociative;
use StrongType\Constraint\Nonempty;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends ArrayType<TKey, TValue>
 */
#[Nonempty]
#[IsAssociative]
class AssociativeArray extends ArrayType
{
}
