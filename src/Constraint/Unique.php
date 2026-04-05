<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Unique implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_array($value) && \count($value) !== \count(\array_unique($value, \SORT_REGULAR))) {
            return "{$short} type must not contain duplicate values";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 100;
    }
}
