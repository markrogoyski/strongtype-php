<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Nonblank;

#[Nonblank]
readonly class NonblankString extends NonemptyString
{
}
