<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

/**
 * RFC 6838 restricted-name format for both type and subtype.
 *
 * Each side: starts with ALPHA/DIGIT, may contain ALPHA/DIGIT/!/#/$/&/-/^/_/./+ in the
 * middle, must end with ALPHA/DIGIT (no trailing punctuation), and is capped at 127 chars.
 *
 * Validates format only; does not verify IANA registry membership. Examples like
 * application/x-foo are syntactically valid even when not registered.
 */
#[Pattern('/\A[A-Za-z0-9](?:[A-Za-z0-9!#$&\-^_.+]{0,125}[A-Za-z0-9])?\/[A-Za-z0-9](?:[A-Za-z0-9!#$&\-^_.+]{0,125}[A-Za-z0-9])?\z/')]
readonly class MimeTypeString extends NonemptyString
{
}
