<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

#[Pattern('/\A\+[1-9]\d{1,14}\z/')]
readonly class PhoneE164String extends NonemptyString
{
}
