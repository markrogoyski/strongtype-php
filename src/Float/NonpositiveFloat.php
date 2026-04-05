<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\Nonpositive;

#[Nonpositive]
readonly class NonpositiveFloat extends FloatingPoint
{
}
