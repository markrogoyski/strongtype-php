<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ConstraintValidator;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements \IteratorAggregate<TKey, TValue>
 */
abstract class ArrayType implements \JsonSerializable, \Countable, \IteratorAggregate, \Stringable, HasEquals
{
    /** @var array<TKey, TValue> */
    public protected(set) array $values;

    /**
     * @param array<TKey, TValue> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
        ConstraintValidator::validate($this, $this->values);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return array<TKey, TValue>
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return $this->values;
    }

    #[\Override]
    public function __toString(): string
    {
        return \json_encode($this->values, \JSON_THROW_ON_ERROR);
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->values);
    }

    /**
     * @return array{values: array<TKey, TValue>}
     */
    public function __debugInfo(): array
    {
        return [
            'values' => $this->values
        ];
    }

    /**
     * @return \ArrayIterator<TKey, TValue>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->values);
    }

    /**
     * Subclasses with divergent constructors (e.g. FixedSizeArray) must override this method;
     * the inherited implementation only works when the subclass constructor accepts exactly
     * `array $values`.
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function tryFrom(mixed $value): ?static
    {
        if (!\is_array($value)) {
            return null;
        }
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            return null;
        }
        try {
            return new static($value); // @phpstan-ignore new.static, return.type
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
        return $this->values === $other->values;
    }

    public static function nullable(mixed $value): \StrongType\Nullable
    {
        return new \StrongType\Nullable(static::class, $value);
    }

    /**
     * Subclasses with divergent constructors (e.g. FixedSizeArray) must override this method;
     * the inherited implementation only works when the subclass constructor accepts exactly
     * `array $values`.
     *
     * @param array<TKey, TValue> $values
     * @psalm-suppress UnsafeInstantiation
     */
    public function withValues(array $values): static
    {
        return new static($values); // @phpstan-ignore new.static, return.type
    }
}
