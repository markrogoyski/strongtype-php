<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Exception\StrongTypeException;
use StrongType\Util\SerializationStateGuard;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends ArrayType<TKey, TValue>
 */
readonly class FixedSizeArray extends ArrayType
{
    // Trait methods are private per using-class, not inherited across the hierarchy, so
    // FixedSizeArray needs its own `use` even though ArrayType already has one -- otherwise
    // $this->assertNoUnhandledSerializationState() below would be calling a method private
    // to ArrayType from FixedSizeArray's scope.
    use SerializationStateGuard;

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

    /**
     * Overrides the base: FixedSizeArray's constructor also requires $size, so
     * ArrayType::__unserialize()'s single-argument $this->__construct($values) call would
     * under-supply it. The wider/stricter $data shape here (an extra required 'size' key)
     * is deliberate, not an LSP oversight, so it's suppressed the same way withValues()
     * above suppresses its own necessary-but-narrower override.
     *
     * @return array{values: array<TKey, TValue>, size: int}
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    #[\Override]
    public function __serialize(): array
    {
        $this->assertNoUnhandledSerializationState(['values', 'size']);
        $this->assertConstructorIsSafeToInherit(2);
        return ['values' => $this->values, 'size' => $this->size];
    }

    /**
     * @param array{values: array<TKey, TValue>, size: int} $data
     * @psalm-suppress ImplementedParamTypeMismatch, DirectConstructorCall Deliberate:
     *     unserialize() creates this object without ever initializing it, so this is the
     *     sole write these properties get, not a re-initialization of an
     *     already-constructed instance.
     */
    #[\Override] // @phpstan-ignore method.childParameterType
    public function __unserialize(array $data): void
    {
        $this->__construct($data['values'], $data['size']);
    }
}
