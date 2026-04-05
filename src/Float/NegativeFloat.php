<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\Negative;

#[Negative]
readonly class NegativeFloat extends FloatingPoint
{
}
