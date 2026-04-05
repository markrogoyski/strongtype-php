<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Alpha;

#[Alpha]
readonly class AlphaString extends NonemptyString
{
}
