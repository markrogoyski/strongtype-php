<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Email implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value) && \filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            return "{$short} type must be a valid email address, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 150;
    }
}
