<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Email;

#[Email]
readonly class EmailString extends NonemptyString
{
}
