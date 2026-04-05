<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Base64;

#[Base64]
readonly class Base64String extends NonemptyString
{
}
