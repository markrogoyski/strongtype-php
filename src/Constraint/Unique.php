<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Unique implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_array($value) && \count($value) !== \count(\array_unique($value, \SORT_REGULAR))) {
            return "{$short} type must not contain duplicate values";
        }

        return null;
    }

    public function priority(): int
    {
        return 100;
    }
}
