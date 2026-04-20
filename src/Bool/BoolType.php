<?php

declare(strict_types=1);

namespace StrongType\Bool;

use StrongType\Constraint\ConstraintValidator;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;

readonly abstract class BoolType implements \JsonSerializable, \Stringable, HasEquals
{
    public function __construct(public bool $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): bool
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }

    /**
     * @return bool[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }

    /**
     * Subclasses with narrower constructor types (e.g. `TrueValue` with `true`, `FalseValue` with `false`)
     * must override this method to pre-filter mismatched literals — otherwise the inherited `new static($value)`
     * will raise an uncaught `\TypeError` for valid-but-wrong-literal input. See `TrueValue::tryFrom()` and
     * `FalseValue::tryFrom()` for the pattern.
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function tryFrom(mixed $value): ?static
    {
        if (!\is_bool($value)) {
            return null;
        }
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            return null;
        }
        try {
            return new static($value); // @phpstan-ignore new.static
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
