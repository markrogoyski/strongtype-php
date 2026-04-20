<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\InRange;

#[InRange(0.0, 100.0)]
readonly class PercentFloat extends FloatingPoint
{
}
