<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\Unique;

#[Unique]
class UniqueArray extends NonemptyArray
{
}
