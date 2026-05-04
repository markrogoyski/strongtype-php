<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class ClassExists implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_string($value) && !\class_exists($value) && !\interface_exists($value) && !\enum_exists($value)) {
            return "{$short} type must be an existing class, interface, or enum, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 150;
    }
}
