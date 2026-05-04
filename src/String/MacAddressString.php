<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/\A([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}\z/')]
readonly class MacAddressString extends NonemptyString
{
}
