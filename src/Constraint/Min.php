<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Min implements ConstraintInterface
{
    public function __construct(
        private int|float $min,
        private bool $exclusive = false,
    ) {
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_numeric($value)) {
            if ($this->exclusive) {
                if ($value <= $this->min) {
                    return "{$short} type must be > {$this->min}, got {$value}";
                }
            } else {
                if ($value < $this->min) {
                    return "{$short} type must be >= {$this->min}, got {$value}";
                }
            }
        }

        return null;
    }

    #[\Override]
    public function priority(): int
    {
        return 50;
    }
}
