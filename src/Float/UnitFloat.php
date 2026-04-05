<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\Max;
use StrongType\Constraint\Min;

#[Min(0.0), Max(1.0)]
readonly class UnitFloat extends FloatingPoint
{
}
