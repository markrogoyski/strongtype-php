<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends ArrayType<TKey, TValue>
 */
readonly class FixedSizeArray extends ArrayType
{
    /**
     * @param array<TKey, TValue> $values
     */
    public function __construct(array $values, private(set) int $size)
    {
        parent::__construct($values);

        if (\count($this->values) !== $this->size) {
            throw new StrongTypeException("FixedSizeArray type must have exactly {$this->size} elements, got " . \count($this->values));
        }
    }

    /**
     * @param array<TKey, TValue> $values
     * @psalm-suppress UnsafeInstantiation
     */
    #[\Override]
    public function withValues(array $values): static
    {
        return new static($values, $this->size); // @phpstan-ignore new.static, return.type
    }

    /**
     * FixedSizeArray requires a size and cannot be constructed from a single value.
     * Use `new FixedSizeArray($values, $size)` directly.
     *
     * @throws \BadMethodCallException always
     */
    #[\Override]
    public static function tryFrom(mixed $value): ?static
    {
        throw new \BadMethodCallException('FixedSizeArray::tryFrom() is unsupported; use "new FixedSizeArray($values, $size)" directly.');
    }

    /**
     * FixedSizeArray requires a size and cannot be wrapped via the single-value Nullable factory.
     * Construct with `new FixedSizeArray($values, $size)` and wrap in Nullable manually if needed.
     *
     * @throws \BadMethodCallException always
     */
    #[\Override]
    public static function nullable(mixed $value): \StrongType\Nullable
    {
        throw new \BadMethodCallException('FixedSizeArray::nullable() is unsupported; construct manually and wrap in Nullable.');
    }
}
