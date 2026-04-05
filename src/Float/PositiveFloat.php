<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\Positive;

#[Positive]
readonly class PositiveFloat extends FloatingPoint
{
}
