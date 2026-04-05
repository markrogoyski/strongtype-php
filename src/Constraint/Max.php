<?php

declare(strict_types=1);

namespace StrongType\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Max implements ConstraintInterface
{
    public function __construct(
        private int|float $max,
        private bool $exclusive = false,
    ) {
    }

    public function validate(mixed $value, string $className): ?string
    {
        $short = \substr($className, \strrpos($className, '\\') + 1);

        if ($this->exclusive) {
            if ($value >= $this->max) {
                return "{$short} type must be < {$this->max}, got {$value}";
            }
        } else {
            if ($value > $this->max) {
                return "{$short} type must be <= {$this->max}, got {$value}";
            }
        }

        return null;
    }

    public function priority(): int
    {
        return 50;
    }
}
