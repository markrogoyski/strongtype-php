<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/^[01]+$/')]
readonly class BinaryString extends NonemptyString
{
}
