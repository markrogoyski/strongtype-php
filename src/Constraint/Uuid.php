<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Uuid implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_string($value) && !\preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            return "{$short} type must be a valid UUID, got {$value}";
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 150;
    }
}
