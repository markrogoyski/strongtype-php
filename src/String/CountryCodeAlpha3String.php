<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

/**
 * Validates ISO 3166-1 alpha-3 format only; does not verify active registry membership.
 * Accepts codes like XYZ that conform to the pattern but are unassigned.
 */
#[Pattern('/^[A-Z]{3}$/')]
readonly class CountryCodeAlpha3String extends NonemptyString
{
}
