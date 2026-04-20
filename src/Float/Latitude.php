<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\InRange;

#[InRange(-90.0, 90.0)]
readonly class Latitude extends FloatingPoint
{
}
