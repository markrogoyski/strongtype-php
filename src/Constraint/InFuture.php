<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class InFuture implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_int($value) && $value <= \time()) {
            return "{$short} type must be in the future, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 100;
    }
}
