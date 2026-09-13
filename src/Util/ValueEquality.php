<?php

declare(strict_types=1);

namespace StrongType\Util;

class ValueEquality
{
    /**
     * Strict comparison that additionally treats two NAN floats as the same value.
     *
     * The NAN allowance is a top-level rule: it applies only to the two arguments passed
     * directly here, not to a NAN nested inside an array element -- delegating to
     * strictSame() for arrays keeps that nested case exactly as strict as native `===`
     * (see docs/semantics.md#equality-semantics for why the two differ).
     *
     * Shared by {@see \StrongType\Constraint\Unique} and
     * {@see \StrongType\Arrays\ArrayType::equalsUnordered()} so the two notions of "same
     * value" stay consistent across the library.
     */
    public static function same(mixed $a, mixed $b): bool
    {
        try {
            return self::sameOrThrow($a, $b);
        } catch (\Error) {
            return false;
        }
    }

    /**
     * Same as {@see same()}, but lets a cyclic-array comparison's \Error propagate instead of
     * swallowing it into "not the same value". For a caller (such as
     * {@see \StrongType\Constraint\Unique}) that needs to distinguish "confirmed distinct"
     * from "could not be determined" rather than treating both the same way.
     */
    public static function sameOrThrow(mixed $a, mixed $b): bool
    {
        if (\is_float($a) && \is_float($b) && \is_nan($a) && \is_nan($b)) {
            return true;
        }
        return self::strictSameOrThrow($a, $b);
    }

    /**
     * Native `===` semantics (NAN !== NAN included, and -- for arrays -- PHP's own
     * pointer-identity fast path, which is what makes comparing a value to itself
     * reflexive without walking every element) for everything except one case: two
     * distinct arrays that both contain a cycle back to themselves. Native `===` throws
     * ("Nesting level too deep") there, because its cycle guard trips on the *second*
     * array while it is still mid-comparison of the first -- a legal, explicitly
     * supported ArrayType element (see __toString()) crashing equality is the bug this
     * exists to close.
     *
     * The catch reports "not equal" rather than attempting its own comparison. This
     * method has no way to detect *where* the cycle is or walk around it safely -- any
     * hand-rolled recursive walk that tries would eventually have to re-enter the same
     * cycle native === just choked on, via the same unbounded recursion, or fall back to
     * an arbitrary depth cap that can only ever report "matched up to here" without ever
     * having actually compared the rest of the structure, which is indistinguishable from
     * a wrong answer whenever the two arrays genuinely differ beyond that cap. Reporting
     * false is the only answer this method can back up: it is never a false claim of
     * equality, which is what actually matters for a value this deliberately supports
     * being constructed with (see __toString()) but has no way to fully compare.
     *
     * This does not protect against merely very deep, non-cyclic nesting driving the C
     * call stack to a hard segfault -- that isn't a catchable condition, so no userland
     * code (this included) can intercept it. It closes the specific, catchable failure
     * mode two distinct self-referential arrays trigger.
     *
     * Used by {@see \StrongType\Arrays\ArrayType::equals()}, which is deliberately
     * NAN-strict for two independently built instances.
     */
    public static function strictSame(mixed $a, mixed $b): bool
    {
        try {
            return self::strictSameOrThrow($a, $b);
        } catch (\Error) {
            // Routed through a separate method (strictSameOrThrow()) rather than doing
            // `$a === $b` directly here: PHPStan cannot prove a same-class method call
            // throws or doesn't, so (unlike when this used to inline the comparison) it no
            // longer considers this catch dead code, and a static-analysis suppression
            // comment naming that non-existent error here would now go unmatched. The
            // empirical Error this guards against is still real (see the class doc above
            // and ValueEqualityTest's self-referential-array cases).
            return false;
        }
    }

    /**
     * Same as {@see strictSame()}, but lets a cyclic-array comparison's \Error propagate
     * instead of swallowing it. Native `===` throws ("Nesting level too deep") comparing two
     * distinct arrays that both contain a cycle back to themselves -- a legal, explicitly
     * supported ArrayType element (see __toString()) -- so this is never a false claim of
     * inequality either: it genuinely could not be determined.
     */
    public static function strictSameOrThrow(mixed $a, mixed $b): bool
    {
        return $a === $b;
    }
}
