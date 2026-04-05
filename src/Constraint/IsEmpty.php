<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class IsEmpty implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_array($value) && \count($value) !== 0) {
            return "{$short} type must be empty, got " . \print_r($value, true);
        }

        return null;
    }

    public function priority(): int
    {
        return 50;
    }
}
