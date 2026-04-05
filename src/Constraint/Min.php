<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Min implements ConstraintInterface
{
    public function __construct(
        private int|float $min,
        private bool $exclusive = false,
    ) {
    }

    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if ($this->exclusive) {
            if ($value <= $this->min) {
                return "{$short} type must be > {$this->min}, got {$value}";
            }
        } else {
            if ($value < $this->min) {
                return "{$short} type must be >= {$this->min}, got {$value}";
            }
        }

        return null;
    }

    public function priority(): int
    {
        return 50;
    }
}
