<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Lowercase;

#[Lowercase]
readonly class LowercaseAlphaString extends AlphaString
{
}
