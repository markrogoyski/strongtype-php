<?php

declare(strict_types=1);

namespace StrongType\Constraint;

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

        if ($this->hasDuplicates($value)) {
            $short = ShortClassName::of($className);
            return "{$short} type must not contain duplicate values";
        }

        return null;
    }

    /**
     * Pairwise strict comparison so uniqueness matches the library's strict-equality
     * semantics: 0 vs '0', true vs 1, and 1 vs '1' are distinct, while two NaN floats
     * are treated as duplicates (see {@see ValueEquality::same()}).
     *
     * @param array<array-key, mixed> $values
     */
    private function hasDuplicates(array $values): bool
    {
        $list = \array_values($values);
        $count = \count($list);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if (ValueEquality::same($list[$i], $list[$j])) {
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
