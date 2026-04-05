<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\IsEmpty;

#[IsEmpty]
class EmptyArray extends ArrayType
{
}
