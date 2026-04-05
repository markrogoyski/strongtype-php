<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Max;
use StrongType\Constraint\Min;

#[Min(1), Max(65535)]
readonly class PortNumber extends Integer
{
}
