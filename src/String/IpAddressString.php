<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\IpAddress;

#[IpAddress]
readonly class IpAddressString extends NonemptyString
{
}
