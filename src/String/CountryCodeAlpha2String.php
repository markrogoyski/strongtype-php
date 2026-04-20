<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

/**
 * Validates ISO 3166-1 alpha-2 format only; does not verify active registry membership.
 * Accepts codes like ZZ that conform to the pattern but are unassigned.
 */
#[Pattern('/^[A-Z]{2}$/')]
readonly class CountryCodeAlpha2String extends NonemptyString
{
}
