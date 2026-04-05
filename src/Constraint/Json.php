<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Json implements ConstraintInterface
{
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value) && !\json_validate($value)) {
            return "{$short} type must be valid JSON, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 150;
    }
}
