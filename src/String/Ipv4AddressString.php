<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Ipv4;

#[Ipv4]
readonly class Ipv4AddressString extends NonemptyString
{
}
