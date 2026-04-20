<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Rfc3339;

#[Rfc3339]
readonly class Rfc3339DateTimeString extends NonemptyString
{
}
