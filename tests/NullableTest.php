<?php

declare(strict_types=1);

namespace StrongType\Tests;

use StrongType\Arrays\FixedSizeArray;
use StrongType\Arrays\NonemptyArray;
use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Exception\StrongTypeException;
use StrongType\HasEquals;
use StrongType\Int\Integer;
use StrongType\Int\PositiveInt;
use StrongType\Nullable;
use StrongType\String\NonemptyString;
use PHPUnit\Framework\Attributes\Test;

/**
 * Satisfies HasEquals and JsonSerializable but has no __toString(), so PHP does not
 * give it the implicit \Stringable interface. Nullable::__toString() would cast it,
 * so the constructor must reject it as a configuration error rather than let a raw
 * \Error surface later.
 */
final readonly class NotStringableStrongType implements \JsonSerializable, HasEquals
{
    public function __construct(private int $value)
    {
    }

    #[\Override]
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    #[\Override]
    public function equals(HasEquals $other): bool
    {
        return $other instanceof self && $other->value === $this->value;
    }
}

/**
 * A perfectly wrappable single-argument strong type whose constructor happens to raise an
 * \ArgumentCountError from deeper in its own body. Nullable must not relabel that as "this
 * type's constructor requires more than one argument" — the arity it checks is this class's,
 * not that of whatever the constructor calls.
 */
final readonly class NestedArgumentCountErrorStrongType implements \JsonSerializable, \Stringable, HasEquals
{
    public function __construct(private int $value)
    {
        /** @phpstan-ignore-next-line intentionally called with too few arguments */
        (static fn(int $a, int $b): int => $a + $b)(1);
    }

    #[\Override]
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->value;
    }

    #[\Override]
    public function equals(HasEquals $other): bool
    {
        return $other instanceof self && $other->value === $this->value;
    }
}

/**
 * Only the first parameter is required; the rest are optional or variadic, so Nullable can
 * still construct it from a single value.
 */
final readonly class OptionalExtrasStrongType implements \JsonSerializable, \Stringable, HasEquals
{
    /** @var list<mixed> */
    private array $rest;

    public function __construct(private int $value, private int $scale = 1, mixed ...$rest)
    {
        $this->rest = \array_values($rest);
    }

    #[\Override]
    public function jsonSerialize(): int
    {
        return $this->value * $this->scale + \count($this->rest);
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->jsonSerialize();
    }

    #[\Override]
    public function equals(HasEquals $other): bool
    {
        return $other instanceof self && $other->value === $this->value;
    }
}

/**
 * Concrete and interface-complete, but Nullable cannot call `new` on it. Without a guard the
 * null case would construct fine and the non-null case would surface a raw \Error.
 */
final readonly class PrivateConstructorStrongType implements \JsonSerializable, \Stringable, HasEquals
{
    private function __construct(private int $value)
    {
    }

    #[\Override]
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->value;
    }

    #[\Override]
    public function equals(HasEquals $other): bool
    {
        return $other instanceof self && $other->value === $this->value;
    }
}

/**
 * No constructor at all: reflection reports none, so the arity and accessibility guards have
 * nothing to check and Nullable must still behave predictably.
 */
final readonly class ConstructorlessStrongType implements \JsonSerializable, \Stringable, HasEquals
{
    #[\Override]
    public function jsonSerialize(): ?int
    {
        return null;
    }

    #[\Override]
    public function __toString(): string
    {
        return '';
    }

    #[\Override]
    public function equals(HasEquals $other): bool
    {
        return $other instanceof self;
    }
}

/** Variadic-only: nothing is required, so a single value is enough. */
final readonly class VariadicOnlyStrongType implements \JsonSerializable, \Stringable, HasEquals
{
    /** @var list<mixed> */
    private array $values;

    public function __construct(mixed ...$values)
    {
        $this->values = \array_values($values);
    }

    #[\Override]
    public function jsonSerialize(): int
    {
        return \count($this->values);
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) \count($this->values);
    }

    #[\Override]
    public function equals(HasEquals $other): bool
    {
        return $other instanceof self && $other->values === $this->values;
    }
}

class NullableTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function testValidValueWithInt()
    {
        // When
        $nullable = new Nullable(PositiveInt::class, 5);

        // Then
        $this->assertSame(5, $nullable->getValue());
        $this->assertFalse($nullable->isNull());
    }

    #[Test]
    public function testValidValueWithNull()
    {
        // When
        $nullable = new Nullable(PositiveInt::class, null);

        // Then
        $this->assertNull($nullable->getValue());
        $this->assertTrue($nullable->isNull());
    }

    #[Test]
    public function testValidValueWithString()
    {
        // When
        $nullable = new Nullable(NonemptyString::class, 'hello');

        // Then
        $this->assertSame('hello', $nullable->getValue());
        $this->assertFalse($nullable->isNull());
    }

    #[Test]
    public function testDebugInfoWithValue()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // When
        $debugInfo = $nullable->__debugInfo();

        // Then
        $this->assertSame(PositiveInt::class, $debugInfo['type']);
        $this->assertSame(5, $debugInfo['value']);
    }

    #[Test]
    public function testDebugInfoWithNull()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, null);

        // When
        $debugInfo = $nullable->__debugInfo();

        // Then
        $this->assertSame(PositiveInt::class, $debugInfo['type']);
        $this->assertNull($debugInfo['value']);
    }

    #[Test]
    public function testStringRepresentationWithValue()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // When
        $stringRepresentation = (string) $nullable;

        // Then
        $this->assertSame('5', $stringRepresentation);
    }

    #[Test]
    public function testStringRepresentationWithNull()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, null);

        // When
        $stringRepresentation = (string) $nullable;

        // Then
        $this->assertSame('null', $stringRepresentation);
    }

    #[Test]
    public function testJsonSerializationWithValue()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // When
        $jsonSerialization = \json_encode($nullable);

        // Then
        $this->assertSame('5', $jsonSerialization);
    }

    #[Test]
    public function testJsonSerializationWithNull()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, null);

        // When
        $jsonSerialization = \json_encode($nullable);

        // Then
        $this->assertSame('null', $jsonSerialization);
    }

    #[Test]
    public function testInvalidValueThrowsWrappedException()
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $nullable = new Nullable(PositiveInt::class, -1);
    }

    #[Test]
    public function testInvalidTypeThrowsConstraintConfigurationException()
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        $nullable = new Nullable('NonExistentClass', 5);
    }

    #[Test]
    public function testNonStringableTypeThrowsConstraintConfigurationException()
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nullable type must reference a valid StrongType class');

        // When
        $nullable = new Nullable(NotStringableStrongType::class, 5);
    }

    #[Test]
    public function testNonStringableTypeIsRejectedEvenForANullValue()
    {
        // Then the wiring guard runs before the value is looked at
        $this->expectException(ConstraintConfigurationException::class);

        // When
        $nullable = new Nullable(NotStringableStrongType::class, null);
    }

    #[Test]
    public function testAbstractTypeThrowsConstraintConfigurationException()
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nullable type must reference a concrete StrongType class');

        // When
        $nullable = new Nullable(Integer::class, 5);
    }

    #[Test]
    public function testMultiArgumentConstructorThrowsConstraintConfigurationException()
    {
        // Then FixedSizeArray's constructor needs ($values, $size), which Nullable cannot supply
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nullable type must reference a StrongType class whose constructor accepts a single value');

        // When
        $nullable = new Nullable(FixedSizeArray::class, [1, 2, 3]);
    }

    #[Test]
    public function testMultiArgumentConstructorIsRejectedEvenForANullValue()
    {
        // Then the arity guard runs up front, like the other wiring guards, so a Nullable
        // that could never hold a value is never constructed
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nullable type must reference a StrongType class whose constructor accepts a single value');

        // When
        $nullable = new Nullable(FixedSizeArray::class, null);
    }

    #[Test]
    public function testInaccessibleConstructorThrowsConstraintConfigurationException()
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nullable type must reference a StrongType class with a public constructor');

        // When
        $nullable = new Nullable(PrivateConstructorStrongType::class, 5);
    }

    #[Test]
    public function testInaccessibleConstructorIsRejectedEvenForANullValue()
    {
        // Then the wiring guard runs before the value, so the null path cannot slip past it
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nullable type must reference a StrongType class with a public constructor');

        // When
        $nullable = new Nullable(PrivateConstructorStrongType::class, null);
    }

    #[Test]
    public function testConstructorlessTypeIsWrappable()
    {
        // When a class declares no constructor there is no arity or visibility to violate
        $nullable = new Nullable(ConstructorlessStrongType::class, 5);

        // Then
        $this->assertInstanceOf(ConstructorlessStrongType::class, $nullable->unwrap());
        $this->assertFalse($nullable->isNull());
    }

    #[Test]
    public function testVariadicOnlyConstructorIsWrappable()
    {
        // When nothing is required, a single value satisfies the constructor
        $nullable = new Nullable(VariadicOnlyStrongType::class, 5);

        // Then
        $this->assertSame(1, $nullable->getValue());
    }

    #[Test]
    public function testOptionalAndVariadicExtraParametersAreStillWrappable()
    {
        // When only the first parameter is required, Nullable can supply it
        $nullable = new Nullable(OptionalExtrasStrongType::class, 7);

        // Then
        $this->assertSame(7, $nullable->getValue());
        $this->assertInstanceOf(OptionalExtrasStrongType::class, $nullable->unwrap());
    }

    /**
     * The arity guard is about the wrapped class's own constructor. An \ArgumentCountError
     * raised from deeper inside a valid one-argument constructor is a different failure and
     * must not be relabelled as a Nullable configuration error.
     */
    #[Test]
    public function testArgumentCountErrorFromInsideTheWrappedConstructorPropagates()
    {
        // Then
        $this->expectException(\ArgumentCountError::class);

        // When
        $nullable = new Nullable(NestedArgumentCountErrorStrongType::class, 5);
    }

    #[Test]
    public function testArgumentCountErrorFromInsideTheWrappedConstructorIsNotAConfigurationError()
    {
        // When
        try {
            new Nullable(NestedArgumentCountErrorStrongType::class, 5);
            $this->fail('Expected \ArgumentCountError');
        } catch (\ArgumentCountError $e) {
            // Then the deeper error surfaces on its own terms
            $this->assertNotInstanceOf(ConstraintConfigurationException::class, $e);
            $this->assertStringNotContainsString('Nullable type must reference', $e->getMessage());
        }
    }

    #[Test]
    public function testUnwrapReturnsTheWrappedStrongType()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // When
        $unwrapped = $nullable->unwrap();

        // Then
        $this->assertInstanceOf(PositiveInt::class, $unwrapped);
        $this->assertSame(5, $unwrapped->getValue());
    }

    #[Test]
    public function testUnwrapReturnsNullForANullValue()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, null);

        // When
        $unwrapped = $nullable->unwrap();

        // Then
        $this->assertNull($unwrapped);
    }

    #[Test]
    public function testUnwrapExposesTheArrayAccessorGetValueCannotReach()
    {
        // Given
        $nullable = new Nullable(NonemptyArray::class, ['a' => 1]);

        // When
        $unwrapped = $nullable->unwrap();

        // Then
        $this->assertInstanceOf(NonemptyArray::class, $unwrapped);
        $this->assertSame(['a' => 1], $unwrapped->getValues());
    }

    #[Test]
    public function testUnwrapIsDistinctFromGetValue()
    {
        // Given
        $nullable = new Nullable(NonemptyString::class, 'hello');

        // When
        $unwrapped = $nullable->unwrap();
        $value = $nullable->getValue();

        // Then getValue() returns the serialized primitive, unwrap() the object itself
        $this->assertSame('hello', $value);
        $this->assertInstanceOf(NonemptyString::class, $unwrapped);
        $this->assertSame('hello', $unwrapped->getValue());
    }

    #[Test]
    public function testUnwrapExposesTheIntersectionTypeMethods()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 7);

        // When
        $unwrapped = $nullable->unwrap();

        // Then the declared intersection type reaches all three interfaces without a cast
        $this->assertNotNull($unwrapped);
        $this->assertSame('7', (string) $unwrapped);
        $this->assertSame(7, $unwrapped->jsonSerialize());
        $this->assertTrue($unwrapped->equals(new PositiveInt(7)));
    }

    #[Test]
    public function testUnwrapReturnsTheSameInstanceOnEveryCall()
    {
        // Given
        $nullable = new Nullable(PositiveInt::class, 5);

        // When
        $first = $nullable->unwrap();

        // Then
        $this->assertSame($first, $nullable->unwrap());
    }
}
