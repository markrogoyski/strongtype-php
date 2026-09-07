<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Max implements ConstraintInterface
{
    use NumericValueType;

    public function __construct(
        private int|float $max,
        private bool $exclusive = false,
    ) {
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_int($value) || \is_float($value)) {
            $max = \var_export($this->max, true);
            if ($this->exclusive) {
                if ($value >= $this->max) {
                    return "{$short} type must be < {$max}, got {$value}";
                }
            } else {
                if ($value > $this->max) {
                    return "{$short} type must be <= {$max}, got {$value}";
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
