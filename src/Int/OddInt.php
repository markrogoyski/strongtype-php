<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Odd;

#[Odd]
readonly class OddInt extends Integer
{
}
