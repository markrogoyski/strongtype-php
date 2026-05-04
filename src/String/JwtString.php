<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/\A[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]*\z/')]
readonly class JwtString extends NonemptyString
{
}
