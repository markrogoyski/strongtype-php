<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Ipv6;

#[Ipv6]
readonly class Ipv6AddressString extends NonemptyString
{
}
