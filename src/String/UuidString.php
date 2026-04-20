<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Uuid;

#[Uuid]
readonly class UuidString extends NonemptyString
{
}
