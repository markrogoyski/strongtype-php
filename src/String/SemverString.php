<?php

declare(strict_types=1);

namespace StrongType\String;

use StrongType\Constraint\Pattern;

// SemVer 2.0.0 spec-compliant regex. Source: https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-string
// Anchored with \A...\z so trailing newline/whitespace cannot satisfy the match (PHP's $ matches before a final \n by default).
#[Pattern('/\A(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?\z/')]
readonly class SemverString extends NonemptyString
{
}
