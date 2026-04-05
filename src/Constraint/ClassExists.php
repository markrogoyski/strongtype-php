<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class ClassExists implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value) && !\class_exists($value) && !\interface_exists($value) && !\enum_exists($value)) {
            return "{$short} type must be an existing class, interface, or enum, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 150;
    }
}
