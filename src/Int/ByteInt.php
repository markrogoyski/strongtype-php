<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Max;
use StrongType\Constraint\Min;

#[Min(0), Max(255)]
readonly class ByteInt extends Integer
{
}
