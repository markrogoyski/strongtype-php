<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DivisibleBy implements ConstraintInterface
{
    public function __construct(private int $divisor)
    {
    }

    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if ($value % $this->divisor !== 0) {
            return "{$short} type must be divisible by {$this->divisor}, got {$value}";
        }

        return null;
    }

    public function priority(): int
    {
        return 50;
    }
}
