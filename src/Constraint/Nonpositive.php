<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Nonpositive implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if ((\is_int($value) || \is_float($value)) && $value > 0) {
            return "{$short} type must be <= 0, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }

    /**
     * @return non-empty-list<ValueType>
     */
    #[\Override]
    public function acceptedValueTypes(): array
    {
        return [ValueType::Int, ValueType::Float];
    }
}
