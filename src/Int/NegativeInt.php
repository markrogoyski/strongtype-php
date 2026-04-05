<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Negative;

#[Negative]
readonly class NegativeInt extends Integer
{
}
