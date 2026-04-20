<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/')]
readonly class MacAddressString extends NonemptyString
{
}
