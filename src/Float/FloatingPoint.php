<?php

declare(strict_types=1);

namespace StrongType\Float;

use StrongType\Constraint\ConstraintValidator;
use StrongType\Constraint\Finite;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;

/**
 * @phpstan-consistent-constructor
 * @psalm-consistent-constructor
 */
#[Finite]
readonly abstract class FloatingPoint implements \JsonSerializable, \Stringable, HasEquals
{
    public function __construct(public float $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): float
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return \strval($this->value);
    }

    /**
     * @return float[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }

    /**
     * Accepts float and int (int is widened to float, matching PHP's implicit
     * int-to-float conversion; precision may be lost for ints beyond 2^53).
     * Numeric strings such as "5" or "5.0" return null — string parsing is
     * locale- and format-sensitive, so callers must cast explicitly. Returns
     * null on constraint failure or non-finite input.
     */
    public static function tryFrom(mixed $value): ?static
    {
        if (!\is_float($value) && !\is_int($value)) {
            return null;
        }
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            return null;
        }
        try {
            return new static((float) $value);
        } catch (StrongTypeException) {
            return null;
        }
    }

    #[\Override]
    public function equals(HasEquals $other): bool
    {
        if (!$other instanceof self || $other::class !== static::class) {
            return false;
        }
        return $this->value === $other->value;
    }

    public static function nullable(mixed $value): \StrongType\Nullable
    {
        return new \StrongType\Nullable(static::class, $value);
    }
}
