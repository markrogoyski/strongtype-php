<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/\A[a-z0-9]+(-[a-z0-9]+)*\z/')]
readonly class SlugString extends NonemptyString
{
}
