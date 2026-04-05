<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/^[a-z0-9]+(-[a-z0-9]+)*$/')]
readonly class SlugString extends NonemptyString
{
}
