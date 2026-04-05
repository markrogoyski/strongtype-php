<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Nonempty;

#[Nonempty]
readonly class NonemptyString extends StringType
{
}
