<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class InRange implements ConstraintInterface
{
    use NumericValueType;

    public function __construct(
        private int|float $min,
        private int|float $max,
        private bool $exclusiveMin = false,
        private bool $exclusiveMax = false,
    ) {
        // Every comparison against NAN is false, so a NAN bound would silently accept
        // every finite value instead of ever engaging -- checked before the min > max
        // comparison below, since NAN > anything is also false and would otherwise slip
        // past that guard unnoticed.
        if (\is_float($this->min) && \is_nan($this->min)) {
            throw new ConstraintConfigurationException('InRange: min must not be NAN');
        }
        if (\is_float($this->max) && \is_nan($this->max)) {
            throw new ConstraintConfigurationException('InRange: max must not be NAN');
        }
        if ($this->min > $this->max) {
            throw new ConstraintConfigurationException("InRange: min ({$this->min}) must not be greater than max ({$this->max})");
        }
        if ($this->min == $this->max && ($this->exclusiveMin || $this->exclusiveMax)) {
            throw new ConstraintConfigurationException("InRange: min and max are equal ({$this->min}) but exclusive flag is set, resulting in an empty range");
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_int($value) || \is_float($value)) {
            $leftBracket  = $this->exclusiveMin ? '(' : '[';
            $rightBracket = $this->exclusiveMax ? ')' : ']';

            $belowMin = $this->exclusiveMin ? $value <= $this->min : $value < $this->min;
            $aboveMax = $this->exclusiveMax ? $value >= $this->max : $value > $this->max;

            if ($belowMin || $aboveMax) {
                $min = \var_export($this->min, true);
                $max = \var_export($this->max, true);
                return "{$short} type must be in range {$leftBracket}{$min}, {$max}{$rightBracket}, got {$value}";
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
