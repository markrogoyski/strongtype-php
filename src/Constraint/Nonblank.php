<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Nonblank implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value) && \trim($value) === '') {
            return "{$short} type must not be blank, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 60;
    }
}
