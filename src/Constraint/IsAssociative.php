<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class IsAssociative implements ConstraintInterface
{
    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, (int) \strrpos($className, '\\') + 1);

        if (\is_array($value) && \array_is_list($value)) {
            return "{$short} type must be an associative array, got " . \print_r($value, true);
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}
