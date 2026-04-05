<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\HexDigits;

#[HexDigits]
readonly class HexString extends NonemptyString
{
}
