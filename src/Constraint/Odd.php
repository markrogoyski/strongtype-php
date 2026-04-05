<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Odd implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if ($value % 2 === 0) {
            return "{$short} type must be odd, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 50;
    }
}
