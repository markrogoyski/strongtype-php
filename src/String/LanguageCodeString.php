<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

/**
 * Validates ISO 639-1 alpha-2 format only; does not verify active registry membership.
 * Accepts codes like zz that conform to the pattern but are unassigned.
 */
#[Pattern('/^[a-z]{2}$/')]
readonly class LanguageCodeString extends NonemptyString
{
}
