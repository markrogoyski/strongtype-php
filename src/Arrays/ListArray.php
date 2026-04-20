<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\IsList;

/**
 * @template TValue
 * @extends ArrayType<int, TValue>
 */
#[IsList]
class ListArray extends ArrayType
{
}
