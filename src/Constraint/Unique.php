<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintEvaluationException;
use StrongType\Util\ShortClassName;
use StrongType\Util\ValueEquality;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Unique implements ConstraintInterface
{
    use ArrayValueType;

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        if (!\is_array($value)) {
            return null;
        }

        $short = ShortClassName::of($className);

        try {
            if ($this->hasDuplicates($value)) {
                return "{$short} type must not contain duplicate values";
            }
        } catch (\Error) {
            // Two elements both self-referential arrays trip PHP's cyclic-array guard
            // ("Nesting level too deep") mid-comparison, so ValueEquality::sameOrThrow()
            // could not determine whether they are duplicates. Unlike
            // ArrayType::equals()/equalsUnordered() -- an informational comparison that can
            // safely default to "not equal" -- Unique is a gatekeeper: silently treating an
            // unprovable pair as "distinct" would let a genuinely duplicated circular
            // structure through. Reject rather than guess -- and throw rather than return a
            // normal violation string, so that a #[Not(new Unique)] composition can't invert
            // "couldn't tell" into "so it must be fine" (see ConstraintEvaluationException).
            throw new ConstraintEvaluationException(
                "{$short} type must not contain elements whose uniqueness cannot be verified (circular array reference)"
            );
        }

        return null;
    }

    /**
     * Pairwise strict comparison so uniqueness matches the library's strict-equality
     * semantics: 0 vs '0', true vs 1, and 1 vs '1' are distinct, while two NaN floats
     * are treated as duplicates (see {@see ValueEquality::sameOrThrow()}).
     *
     * Uses the *Or*Throw() variant deliberately: a cyclic-array comparison's \Error must
     * reach validate() above rather than being swallowed into "not a duplicate" here.
     *
     * @param array<array-key, mixed> $values
     */
    private function hasDuplicates(array $values): bool
    {
        $list = \array_values($values);
        $count = \count($list);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if (ValueEquality::sameOrThrow($list[$i], $list[$j])) {
                    return true;
                }
            }
        }
        return false;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
