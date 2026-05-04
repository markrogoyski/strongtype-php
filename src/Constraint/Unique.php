<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Unique implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        if (!\is_array($value)) {
            return null;
        }

        if ($this->hasDuplicates($value)) {
            $short = ShortClassName::of($className);
            return "{$short} type must not contain duplicate values";
        }

        return null;
    }

    /**
     * Pairwise strict comparison so uniqueness matches the library's strict-equality
     * semantics: 0 vs '0', true vs 1, and 1 vs '1' are distinct, while two NaN floats
     * are treated as duplicates (PHP's `===` says NAN !== NAN, but they are
     * indistinguishable as values for the purposes of "no duplicates").
     *
     * @param array<array-key, mixed> $values
     */
    private function hasDuplicates(array $values): bool
    {
        $list = \array_values($values);
        $count = \count($list);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->same($list[$i], $list[$j])) {
                    return true;
                }
            }
        }
        return false;
    }

    private function same(mixed $a, mixed $b): bool
    {
        if (\is_float($a) && \is_float($b) && \is_nan($a) && \is_nan($b)) {
            return true;
        }
        return $a === $b;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
