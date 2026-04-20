<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\InRange;

#[InRange(-180.0, 180.0)]
readonly class Longitude extends FloatingPoint
{
}
