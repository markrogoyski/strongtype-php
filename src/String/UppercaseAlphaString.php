<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Uppercase;

#[Uppercase]
readonly class UppercaseAlphaString extends AlphaString
{
}
