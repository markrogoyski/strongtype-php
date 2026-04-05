<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\Nonnegative;

#[Nonnegative]
readonly class NonnegativeFloat extends FloatingPoint
{
}
