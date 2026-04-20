<?php

declare(strict_types=1);

namespace StrongType\DateTime;

use StrongType\Constraint\ConstraintValidator;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;

/**
 * @phpstan-consistent-constructor
 * @psalm-consistent-constructor
 */
readonly abstract class DateTime implements \JsonSerializable, \Stringable, HasEquals
{
    public function __construct(public string $value)
    {
        ConstraintValidator::validate($this, $this->value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return string[]
     */
    public function __debugInfo(): array
    {
        return [
            'value' => $this->value
        ];
    }

    public static function tryFrom(mixed $value): ?static
    {
        if (!\is_string($value)) {
            return null;
        }
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            return null;
        }
        try {
            return new static($value);
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
