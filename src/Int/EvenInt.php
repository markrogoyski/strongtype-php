<?php

declare(strict_types=1);

namespace StrongType\Int;

use StrongType\Constraint\Even;

#[Even]
readonly class EvenInt extends Integer
{
}
