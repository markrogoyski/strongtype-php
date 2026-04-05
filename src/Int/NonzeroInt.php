<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Nonzero;

#[Nonzero]
readonly class NonzeroInt extends Integer
{
}
