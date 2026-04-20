<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\IsEmpty;

/**
 * @extends ArrayType<array-key, never>
 */
#[IsEmpty]
class EmptyArray extends ArrayType
{
}
