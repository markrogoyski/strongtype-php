<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Min implements ConstraintInterface
{
    use NumericValueType;

    public function __construct(
        private int|float $min,
        private bool $exclusive = false,
    ) {
        // Every comparison against NAN is false, so a NAN bound would silently accept
        // every finite value instead of ever engaging -- the same fail-open shape the
        // value-type applicability check exists to close, just via a bad argument instead
        // of a misapplied attribute.
        if (\is_float($this->min) && \is_nan($this->min)) {
            throw new ConstraintConfigurationException('Min: bound must not be NAN');
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_int($value) || \is_float($value)) {
            $min = \var_export($this->min, true);
            if ($this->exclusive) {
                if ($value <= $this->min) {
                    return "{$short} type must be > {$min}, got {$value}";
                }
            } else {
                if ($value < $this->min) {
                    return "{$short} type must be >= {$min}, got {$value}";
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
