<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\ClassExists;

#[ClassExists]
readonly class ClassString extends NonemptyString
{
}
