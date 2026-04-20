<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Cidr;

#[Cidr]
readonly class CidrString extends NonemptyString
{
}
