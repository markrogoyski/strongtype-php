<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class IsEmpty implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_array($value) && \count($value) !== 0) {
            return "{$short} type must be empty, got an array with " . \count($value) . " element(s)";
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
        return [ValueType::Array];
    }
}
