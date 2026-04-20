<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\InRange;

#[InRange(100, 599)]
readonly class HttpStatusCode extends Integer
{
}
