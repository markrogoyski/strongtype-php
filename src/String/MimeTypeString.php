<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

/**
 * RFC 6838 restricted-name format for both type and subtype.
 *
 * Each side: restricted-name-first (ALPHA/DIGIT) followed by 0-126 restricted-name-chars
 * (ALPHA/DIGIT/!/#/$/&/-/^/_/./+), capped at 127 chars total. Only the first character is
 * restricted -- the grammar imposes no constraint on the last, so trailing punctuation
 * (e.g. application/x-foo-, application/x-foo+) is syntactically valid and accepted here.
 *
 * Validates format only; does not verify IANA registry membership. Examples like
 * application/x-foo are syntactically valid even when not registered.
 */
#[Pattern('/\A[A-Za-z0-9][A-Za-z0-9!#$&\-^_.+]{0,126}\/[A-Za-z0-9][A-Za-z0-9!#$&\-^_.+]{0,126}\z/')]
readonly class MimeTypeString extends NonemptyString
{
}
