<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Base64 implements ConstraintInterface
{
    use StringValueType;

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        if (!\is_string($value)) {
            return null;
        }

        $short = ShortClassName::of($className);
        $error = "{$short} type must be valid base64, got {$value}";

        // RFC 4648 §4 canonical form. base64_decode(..., true) rejects the standard
        // alphabet's illegal characters, including the base64url alphabet (-/_), but
        // strict mode alone does not enforce a length that's a multiple of 4 or
        // correctly placed padding -- e.g. 'YQ' (no padding) and 'YWI' (no padding)
        // both decode successfully. The re-encode round trip is what actually
        // enforces canonical length/padding/whitespace, and also catches
        // non-canonical trailing bits a permissive decoder accepts on its own (e.g.
        // 'YR==' decodes to a byte that re-encodes as 'YQ=='). No regex prefilter:
        // PCRE can hit its JIT stack limit on very large inputs, and preg_match()
        // returning false for that (rather than 0) would otherwise need the same
        // ConstraintEvaluationException handling Pattern/NotPattern use --
        // decode+round-trip needs no such escape hatch, since it never fails to
        // evaluate.
        $decoded = \base64_decode($value, true);
        if ($decoded === false || \base64_encode($decoded) !== $value) {
            return $error;
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
