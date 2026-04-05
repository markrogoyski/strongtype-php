<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Positive;

#[Positive]
readonly class PositiveInt extends Integer
{
}
