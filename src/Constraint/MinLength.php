<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class MinLength implements ConstraintInterface
{
    public function __construct(private int $minLength)
    {
    }

    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if (\is_string($value) && \strlen($value) < $this->minLength) {
            return "{$short} type must have length >= {$this->minLength}, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 50;
    }
}
