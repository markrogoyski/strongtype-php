<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\NumericDigits;

#[NumericDigits]
readonly class NumericString extends NonemptyString
{
}
