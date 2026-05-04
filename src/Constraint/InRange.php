<?php

declare(strict_types=1);

namespace StrongType\Constraint;

use StrongType\Util\ShortClassName;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class InRange implements ConstraintInterface
{
    public function __construct(
        private int|float $min,
        private int|float $max,
        private bool $exclusiveMin = false,
        private bool $exclusiveMax = false,
    ) {
        if ($this->min > $this->max) {
            throw new \LogicException("InRange: min ({$this->min}) must not be greater than max ({$this->max})");
        }
        if ($this->min == $this->max && ($this->exclusiveMin || $this->exclusiveMax)) {
            throw new \LogicException("InRange: min and max are equal ({$this->min}) but exclusive flag is set, resulting in an empty range");
        }
    }

    #[\Override]
    public function validate(mixed $value, string $className): ?string
    {
        $short = ShortClassName::of($className);

        if (\is_numeric($value)) {
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
