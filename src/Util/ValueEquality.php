<?php

declare(strict_types=1);

namespace StrongType\Util;

class ValueEquality
{
    /**
     * Strict comparison that treats two NAN floats as the same value.
     *
     * PHP's `===` says NAN !== NAN, which would make "no duplicates" and "same element"
     * checks non-reflexive for a NAN-containing array. Shared by
     * {@see \StrongType\Constraint\Unique} and {@see \StrongType\Arrays\ArrayType} so the
     * two notions of "same value" stay consistent across the library.
     */
    public static function same(mixed $a, mixed $b): bool
    {
        if (\is_float($a) && \is_float($b) && \is_nan($a) && \is_nan($b)) {
            return true;
        }
        return $a === $b;
    }
}
