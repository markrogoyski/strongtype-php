<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class ExactCount implements ConstraintInterface
{
    public function __construct(private int $count)
    {
    }

    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_array($value) && \count($value) !== $this->count) {
            return "{$short} type must have exactly {$this->count} elements, got " . \count($value);
        }

        return null;
    }

    public function priority(): int
    {
        return 50;
    }
}
