<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Nonpositive;

#[Nonpositive]
readonly class NonpositiveInt extends Integer
{
}
