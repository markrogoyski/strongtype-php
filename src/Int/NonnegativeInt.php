<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Nonnegative;

#[Nonnegative]
readonly class NonnegativeInt extends Integer
{
}
