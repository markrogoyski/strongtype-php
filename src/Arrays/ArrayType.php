<?php

declare(strict_types=1);

namespace StrongType\Arrays;

use StrongType\Constraint\ConstraintValidator;
use StrongType\Constraint\ValueType;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;
use StrongType\Util\SerializationStateGuard;
use StrongType\Util\Stringify;
use StrongType\Util\ValueEquality;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements \IteratorAggregate<TKey, TValue>
 */
readonly abstract class ArrayType implements \JsonSerializable, \Countable, \IteratorAggregate, \Stringable, HasEquals
{
    use SerializationStateGuard;

    final public const ValueType VALUE_TYPE = ValueType::Array;

    /** @var array<TKey, TValue> */
    public array $values;

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

    /**
     * Maximum nesting depth for the fallback stringifier. Bounded so a
     * self-referential array (NonemptyArray can validly wrap one) cannot drive
     * the renderer into unbounded recursion.
     */
    private const int STRINGIFY_MAX_DEPTH = 64;

    #[\Override]
    public function __toString(): string
    {
        // Stringable contract requires __toString() to never throw. json_encode
        // refuses NAN, INF, -INF, and resources (legal in ArrayOfFloats /
        // ArrayOfResources / etc.), throws on self-referential arrays, and
        // propagates anything thrown by an inner JsonSerializable::jsonSerialize().
        // Catch the lot and fall back to a recursion-bounded renderer.
        try {
            return \json_encode($this->values, \JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return self::stringifyJsonish($this->values, 0);
        }
    }

    /**
     * Depth is not re-checked here: stringifyJsonishValue() is the sole gateway
     * into this method (and into every recursive descent), and it enforces
     * STRINGIFY_MAX_DEPTH before each hop, so the bound is already guaranteed.
     *
     * @param array<array-key, mixed> $values
     */
    private static function stringifyJsonish(array $values, int $depth): string
    {
        $isList = \array_is_list($values);
        $parts = [];
        foreach ($values as $key => $value) {
            $rendered = self::stringifyJsonishValue($value, $depth + 1);
            if ($isList) {
                $parts[] = $rendered;
                continue;
            }
            $encodedKey = \json_encode((string) $key);
            $parts[] = ($encodedKey === false ? '"' . (string) $key . '"' : $encodedKey) . ':' . $rendered;
        }
        return ($isList ? '[' : '{') . \implode(',', $parts) . ($isList ? ']' : '}');
    }

    private static function stringifyJsonishValue(mixed $value, int $depth): string
    {
        if ($depth >= self::STRINGIFY_MAX_DEPTH) {
            return '"*RECURSION*"';
        }
        if (\is_array($value)) {
            return self::stringifyJsonish($value, $depth);
        }
        if (\is_float($value)) {
            if (\is_nan($value)) {
                return 'NaN';
            }
            if (\is_infinite($value)) {
                return $value > 0 ? 'Infinity' : '-Infinity';
            }
        }
        if (\is_resource($value)) {
            return 'resource(#' . \get_resource_id($value) . ')';
        }
        // Drain JsonSerializable manually so exceptions from user jsonSerialize()
        // implementations (which json_encode would otherwise propagate) cannot
        // escape __toString().
        if ($value instanceof \JsonSerializable) {
            try {
                $serialized = $value->jsonSerialize();
            } catch (\Throwable) {
                return self::renderObjectMarker($value);
            }
            // Increment depth so a jsonSerialize() that returns $this (or any
            // structure that re-enters itself) cannot loop forever.
            return self::stringifyJsonishValue($serialized, $depth + 1);
        }
        if (\is_object($value)) {
            // Plain objects: json_encode public properties rather than
            // Stringify::value() (which renders a bare type name), since __toString()
            // aims for a JSON-shaped representation here. json_encode can still
            // propagate from a nested JsonSerializable, so guard it.
            // JSON_THROW_ON_ERROR makes encoding failures throw JsonException
            // (caught by the same handler) and signals to static analysis that
            // the call is throwing.
            try {
                return \json_encode($value, \JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return self::renderObjectMarker($value);
            }
        }
        $encoded = \json_encode($value);
        return $encoded === false ? Stringify::value($value) : $encoded;
    }

    private static function renderObjectMarker(object $value): string
    {
        return 'object(' . $value::class . ')';
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
        // Routed through ValueEquality::strictSame() rather than bare === -- a
        // self-referential array is a legal, explicitly supported element (see
        // __toString() above), and native === throws ("Nesting level too deep") comparing
        // two distinct ones. strictSame() tries native === first (so every existing
        // behavior, including the reflexive $a->equals($a) case with NAN, is unchanged)
        // and only reports false on that specific thrown Error, rather than crash. It
        // keeps NAN !== NAN, preserving equals()'s existing divergence from
        // equalsUnordered() (see docs/semantics.md#equality-semantics).
        return ValueEquality::strictSame($this->values, $other->values);
    }

    /**
     * Type-strict equality that ignores order and keys at the top level.
     *
     * Both arrays must contain the same multiset of values (each value the same number of times),
     * compared with strict equality. Nested arrays are compared as-is, so element ordering inside
     * nested structures still matters. Use {@see equals()} when key/order significance is required.
     */
    public function equalsUnordered(HasEquals $other): bool
    {
        if (!$other instanceof self || $other::class !== static::class) {
            return false;
        }
        if (\count($this->values) !== \count($other->values)) {
            return false;
        }
        $remaining = \array_values($other->values);
        foreach ($this->values as $value) {
            $found = false;
            foreach ($remaining as $index => $candidate) {
                if (ValueEquality::same($candidate, $value)) {
                    unset($remaining[$index]);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }
        return true;
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

    /**
     * Native unserialize() populates the readonly $values property directly, bypassing
     * the constructor entirely -- a crafted payload (or a resource silently downgraded to
     * int(0) by serialize()) would otherwise produce an unvalidated instance.
     * __unserialize() re-enters via $this->__construct() (virtual dispatch, so a
     * subclass's own checks run too), which is also the only write this property ever
     * gets, satisfying readonly's write-once rule. FixedSizeArray overrides both methods
     * since its constructor also requires $size.
     *
     * @return array{values: array<TKey, TValue>}
     */
    public function __serialize(): array
    {
        $this->assertNoUnhandledSerializationState(['values']);
        $this->assertConstructorIsSafeToInherit(1);
        return ['values' => $this->values];
    }

    /**
     * @param array{values: array<TKey, TValue>} $data
     * @psalm-suppress DirectConstructorCall Deliberate: unserialize() creates this object
     *     without ever initializing it, so this is the sole write this property gets, not
     *     a re-initialization of an already-constructed instance.
     */
    public function __unserialize(array $data): void
    {
        $this->__construct($data['values']);
    }
}
