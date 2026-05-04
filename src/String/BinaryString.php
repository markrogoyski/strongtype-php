<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/\A[01]+\z/')]
readonly class BinaryString extends NonemptyString
{
}
