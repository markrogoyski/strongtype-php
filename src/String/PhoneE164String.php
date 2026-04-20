<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/^\+[1-9]\d{1,14}$/')]
readonly class PhoneE164String extends NonemptyString
{
}
