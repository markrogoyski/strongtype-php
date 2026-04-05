<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Alphanumeric;

#[Alphanumeric]
readonly class AlphanumericString extends NonemptyString
{
}
