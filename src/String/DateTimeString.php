<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\DateTimeParseable;

#[DateTimeParseable]
readonly class DateTimeString extends NonemptyString
{
}
