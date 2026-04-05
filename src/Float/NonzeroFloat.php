<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\Nonzero;

#[Nonzero]
readonly class NonzeroFloat extends FloatingPoint
{
}
