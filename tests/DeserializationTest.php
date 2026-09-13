<?php

declare(strict_types=1);

namespace StrongType\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Arrays\FixedSizeArray;
use StrongType\Arrays\NonemptyArray;
use StrongType\Bool\TrueValue;
use StrongType\DateTime\DateString;
use StrongType\DateTime\FutureTimestamp;
use StrongType\DateTime\Timestamp;
use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Exception\StrongTypeException;
use StrongType\Float\PositiveFloat;
use StrongType\HasEquals;
use StrongType\Int\Integer;
use StrongType\Int\PositiveInt;
use StrongType\Nullable;
use StrongType\String\EmptyString;
use StrongType\String\NonemptyString;
use StrongType\String\StringType;

/**
 * A wrapped type whose jsonSerialize() output diverges from its constructor input --
 * legal per the \JsonSerializable contract. Used to lock in that Nullable's round trip
 * reconstructs from the wrapped object's own state, not from getValue()/jsonSerialize(),
 * which would silently produce a different value than the one originally wrapped.
 */
readonly class CaseNormalizingTestString extends StringType
{
    #[\Override]
    public function jsonSerialize(): string
    {
        return \strtoupper($this->value);
    }
}

/**
 * Implements the three interfaces Nullable requires (HasEquals, JsonSerializable,
 * Stringable) but not __serialize()/__unserialize() -- a legitimate, fully supported way
 * to use Nullable for construction, equality, and JSON (see NullableTest's own
 * ConstructorlessStrongType/VariadicOnlyStrongType/OptionalExtrasStrongType fixtures), but
 * one Nullable's own serialization must refuse to trust rather than silently accept.
 */
final readonly class UnsafeNegativeRejectingType implements \JsonSerializable, \Stringable, HasEquals
{
    public function __construct(private int $value)
    {
        if ($value < 0) {
            throw new StrongTypeException("UnsafeNegativeRejectingType must be >= 0, got {$value}");
        }
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
 * Adds constructor state (scale) beyond Integer's own `value` without overriding
 * __serialize()/__unserialize() to carry it -- exercises
 * StrongType\Util\SerializationStateGuard's rejection.
 */
readonly class ScaledIntMissingSerializationOverride extends Integer
{
    public function __construct(int $value, public int $scale = 2)
    {
        parent::__construct($value);
    }
}

/**
 * Overrides Integer's constructor to *transform* its input (scale by 100) rather than
 * store it verbatim, without declaring any additional property -- so
 * SerializationStateGuard's property-hierarchy check alone would see nothing wrong.
 * Exercises assertConstructorIsSafeToInherit()'s separate, reflection-only check: since
 * this override hasn't certified constructorPreservesSerializedStateVerbatim(), it's
 * rejected before ever reaching a point where inherited __unserialize() could silently
 * reconstruct value=50000 from a serialized value=500.
 */
readonly class TransformingIntMissingSerializationOverride extends Integer
{
    public function __construct(int $value)
    {
        parent::__construct($value * 100);
    }
}

/**
 * Overrides Timestamp's constructor to *transform* its input (scale by 100) while
 * inheriting Timestamp's own constructorPreservesSerializedStateVerbatim() certification
 * unchanged -- exercises that the certification is bound to the specific class that
 * declares the constructor it describes, not inherited transitively. Timestamp certified
 * only that *its own* constructor (validation-only) is safe; that certification does not
 * carry over to this class's different, transforming constructor just because this class
 * didn't bother to override the certification method too.
 */
readonly class ScaledTimestampMissingItsOwnCertification extends Timestamp
{
    public function __construct(int $value)
    {
        parent::__construct($value * 100);
    }
}

/**
 * Overrides Integer's constructor to require a second, *unstored* argument -- inherited
 * __unserialize() calls $this->__construct($data['value']) with only one, so this would
 * otherwise fail with \ArgumentCountError deep inside deserialization instead of a clear
 * error the moment someone tries to serialize it.
 */
readonly class RequiresExtraUnstoredArgIntMissingSerializationOverride extends Integer
{
    public function __construct(int $value, string $unit)
    {
        parent::__construct($value);
    }
}

/**
 * Overrides Integer's constructor as *private*, gated behind a public static factory, and
 * truthfully certifies constructorPreservesSerializedStateVerbatim() -- the constructor
 * really does only validate and store its argument verbatim, so every check
 * assertConstructorIsSafeToInherit() ran before visibility was checked would have passed.
 * Exercises the visibility check specifically: inherited __unserialize() calls
 * $this->__construct() from Integer's own scope, and private access is scoped to the exact
 * declaring class, not an ancestor -- unlike protected, which an ancestor scope can reach.
 */
readonly class PrivateConstructorIntMissingSerializationOverride extends Integer
{
    private function __construct(int $value)
    {
        parent::__construct($value);
    }

    public static function of(int $value): self
    {
        return new self($value);
    }

    #[\Override]
    protected static function constructorPreservesSerializedStateVerbatim(): bool
    {
        return true;
    }
}

/**
 * Positive control for PrivateConstructorIntMissingSerializationOverride: identical except
 * the constructor is `protected`, not `private`. Exercises that assertConstructorIsSafeToInherit()
 * does not overreject -- protected really is safe to inherit, since inherited __unserialize()
 * calls $this->__construct() from Integer's own scope, an ancestor of this class, which is
 * exactly the relationship PHP's protected-access rule permits.
 */
readonly class ProtectedConstructorCertifiedInt extends Integer
{
    protected function __construct(int $value)
    {
        parent::__construct($value);
    }

    public static function of(int $value): self
    {
        return new self($value);
    }

    #[\Override]
    protected static function constructorPreservesSerializedStateVerbatim(): bool
    {
        return true;
    }
}

/** Intermediate ancestor declaring `scale` as a *private* property. */
readonly class PrivatelyScaledIntIntermediate extends Integer
{
    public function __construct(int $value, private int $scale = 2)
    {
        parent::__construct($value);
    }
}

/**
 * Adds nothing of its own -- exercises that SerializationStateGuard catches a private
 * property declared by an intermediate ancestor two levels up, not just the runtime
 * class's own properties. ReflectionClass::getProperties() called only on this class would
 * miss `scale` entirely, since a parent's private property isn't visible from a more
 * distant descendant's own reflection.
 */
readonly class PrivatelyScaledIntGrandchild extends PrivatelyScaledIntIntermediate
{
}

/**
 * Declares safe __serialize()/__unserialize() hooks *and* implements the legacy
 * \Serializable interface with an unsafe unserialize() that bypasses the constructor --
 * exercises that Nullable::assertSafelySerializable() rejects \Serializable regardless of
 * the safe modern hooks sitting right next to it, since a crafted payload's own format
 * sigil (not which methods the class declares) picks which path native unserialize() takes.
 */
final readonly class SafeHooksButAlsoLegacySerializableType implements
    \JsonSerializable,
    \Stringable,
    HasEquals,
    \Serializable
{
    public function __construct(private int $value)
    {
        if ($value < 0) {
            throw new StrongTypeException("SafeHooksButAlsoLegacySerializableType must be >= 0, got {$value}");
        }
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

    /**
     * @return array{value: int}
     */
    public function __serialize(): array
    {
        return ['value' => $this->value];
    }

    /**
     * @param array{value: int} $data
     */
    public function __unserialize(array $data): void
    {
        $this->__construct($data['value']);
    }

    // Legacy Serializable pair -- deliberately unsafe: writes straight into the property,
    // bypassing the constructor entirely. Selected by a 'C:' sigil payload regardless of
    // the safe modern hooks above.
    public function serialize(): ?string
    {
        return null;
    }

    public function unserialize(string $data): void
    {
        $this->value = (int) $data;
    }
}

/**
 * Adds constructor state (label) beyond Nullable's own `type`/`strongType` without
 * overriding __serialize()/__unserialize() at all -- exercises
 * Nullable::assertNotInheritingUnsafeSerialization()'s rejection of inherited hooks.
 */
readonly class NullableWithUnhandledExtraState extends Nullable
{
    public function __construct(string $type, mixed $value, public string $label)
    {
        parent::__construct($type, $value);
    }
}

/**
 * A Nullable subclass whose constructor invariant (forbidding null) must not be
 * bypassable via a crafted payload naming this class directly -- see
 * Nullable::assertNotInheritingUnsafeSerialization().
 */
readonly class NullForbiddingNullableSubclass extends Nullable
{
    public function __construct(string $type, mixed $value)
    {
        if ($value === null) {
            throw new StrongTypeException('NullForbiddingNullableSubclass must not wrap null');
        }
        parent::__construct($type, $value);
    }
}

/**
 * A Nullable subclass that also implements the legacy \Serializable interface -- exercises
 * that assertNotInheritingUnsafeSerialization() fails fast on it, the same threat
 * Nullable::assertSafelySerializable() already guards against for a *wrapped* type.
 */
readonly class NullableAlsoImplementingLegacySerializable extends Nullable implements \Serializable
{
    public function serialize(): ?string
    {
        return null;
    }

    public function unserialize(string $data): void
    {
    }
}

/**
 * The documented remedy for assertNotInheritingUnsafeSerialization(): overrides both hooks
 * itself, delegating to parent::__serialize()/parent::__unserialize() to carry the base
 * fields while also carrying its own extra property. Late static binding still reports
 * static::class as this subclass *inside* the delegated-to parent call -- the guard must
 * recognize that __serialize()/__unserialize() are overridden here (via their actual
 * declaring class, not a static::class identity check) and let this through rather than
 * mistaking the delegation for "still inherited unchanged".
 */
readonly class ProperlyOverridingNullableSubclass extends Nullable
{
    public string $label;

    public function __construct(string $type, mixed $value, string $label = 'default')
    {
        parent::__construct($type, $value);
        $this->label = $label;
    }

    /**
     * @return array{type: class-string<\JsonSerializable&\Stringable&HasEquals>, strongType: (\JsonSerializable&\Stringable&HasEquals)|null, label: string}
     * @psalm-suppress ImplementedReturnTypeMismatch Deliberate: a wider, additional-property
     *     shape, the same way FixedSizeArray's own __serialize() override widens its parent's.
     */
    #[\Override]
    public function __serialize(): array
    {
        return parent::__serialize() + ['label' => $this->label];
    }

    /**
     * @param array{type: class-string<\JsonSerializable&\Stringable&HasEquals>, strongType: (\JsonSerializable&\Stringable&HasEquals)|null, label: string} $data
     * @psalm-suppress ImplementedParamTypeMismatch Deliberate, mirroring __serialize() above.
     */
    #[\Override]
    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);
        $this->label = $data['label'];
    }
}

/**
 * Extends ProperlyOverridingNullableSubclass -- which itself properly overrides both
 * hooks -- without adding its own __serialize()/__unserialize() override, but does add a
 * further property. Exercises that Nullable::assertNotInheritingUnsafeSerialization()
 * catches state added *below* whichever ancestor's override is actually running, not just
 * "no override anywhere above Nullable at all": ProperlyOverridingNullableSubclass's own
 * __serialize()/__unserialize() were written before this class -- and its `tag` property
 * -- existed, so they cannot be trusted to carry it.
 */
readonly class NullableGrandchildWithUnhandledExtraState extends ProperlyOverridingNullableSubclass
{
    public function __construct(string $type, mixed $value, string $label, public string $tag)
    {
        parent::__construct($type, $value, $label);
    }
}

/**
 * Extends ProperlyOverridingNullableSubclass without adding its own
 * __serialize()/__unserialize() override *or* any new property -- only its own constructor
 * validation (forbidding null). A property-only check could never see this: exercises that
 * Nullable::assertNotInheritingUnsafeSerialization() separately rejects a constructor
 * override declared below whichever ancestor's hooks are actually running.
 */
readonly class NullableGrandchildWithUnhandledConstructorValidation extends ProperlyOverridingNullableSubclass
{
    public function __construct(string $type, mixed $value, string $label = 'default')
    {
        if ($value === null) {
            throw new StrongTypeException('NullableGrandchildWithUnhandledConstructorValidation must not wrap null');
        }
        parent::__construct($type, $value, $label);
    }
}

/**
 * Native unserialize() populates readonly properties directly, bypassing the
 * constructor -- and therefore ConstraintValidator -- entirely. Every base implements
 * __serialize()/__unserialize() so unserializing re-enters via the constructor (see
 * Integer::__unserialize() and friends), re-running every invariant a valid instance of
 * the concrete class would have to pass, not just the base's own rules.
 */
class DeserializationTest extends TestCase
{
    // Round trips: unserialize(serialize($x)) reproduces an equal, valid instance.

    #[Test]
    public function testIntegerRoundTrips(): void
    {
        // Given
        $original = new PositiveInt(5);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(PositiveInt::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    #[Test]
    public function testFloatRoundTrips(): void
    {
        // Given
        $original = new PositiveFloat(1.5);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(PositiveFloat::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    #[Test]
    public function testStringRoundTrips(): void
    {
        // Given
        $original = new NonemptyString('hello');

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(NonemptyString::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    #[Test]
    public function testBoolRoundTrips(): void
    {
        // Given
        $original = new TrueValue(true);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(TrueValue::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    #[Test]
    public function testDateTimeRoundTrips(): void
    {
        // Given
        $original = new DateString('2024-01-01');

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(DateString::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    #[Test]
    public function testArrayRoundTrips(): void
    {
        // Given
        $original = new NonemptyArray([1, 2, 3]);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(NonemptyArray::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    /**
     * Regression test: FutureTimestamp's constructor override validates against time()
     * (external, mutable state) but stores $value verbatim -- it certifies
     * constructorPreservesSerializedStateVerbatim() precisely so this legitimate,
     * validation-only override keeps round-tripping via inherited __serialize()/
     * __unserialize(), unlike a transforming override (see
     * TransformingIntMissingSerializationOverride below).
     */
    #[Test]
    public function testFutureTimestampRoundTripsDespiteItsValidationOnlyConstructorOverride(): void
    {
        // Given
        $original = new FutureTimestamp(\time() + 3600);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(FutureTimestamp::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    #[Test]
    public function testFixedSizeArrayRoundTripsIncludingSize(): void
    {
        // Given
        $original = new FixedSizeArray([1, 2, 3], 3);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(FixedSizeArray::class, $restored);
        $this->assertSame(3, $restored->size);
        $this->assertTrue($original->equals($restored));
    }

    #[Test]
    public function testNullableRoundTrips(): void
    {
        // Given
        $original = new Nullable(PositiveInt::class, 5);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(Nullable::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    #[Test]
    public function testNullableRoundTripsWhenNull(): void
    {
        // Given
        $original = new Nullable(PositiveInt::class, null);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(Nullable::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    /**
     * Regression test: an earlier version rejected this exact round trip. The constructor
     * accepts a leading '\' (it names the same runtime class as the canonical spelling),
     * but the wrapped-value revalidation compared the raw, non-canonical string against
     * $strongType::class, which is always canonical -- so a Nullable built this way failed
     * to deserialize even though it was perfectly valid.
     */
    #[Test]
    public function testNullableRoundTripsWhenConstructedWithANonCanonicalTypeString(): void
    {
        // Given
        $original = new Nullable('\\' . PositiveInt::class, 5);

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(Nullable::class, $restored);
        $this->assertTrue($original->equals($restored));
    }

    /**
     * Regression test: an earlier version derived the round-tripped value from
     * getValue()/jsonSerialize() instead of the wrapped object's own state, which is lossy
     * whenever the two diverge.
     */
    #[Test]
    public function testNullableRoundTripPreservesTheWrappedValueEvenWhenJsonSerializeDiverges(): void
    {
        // Given
        $original = new Nullable(CaseNormalizingTestString::class, 'MixedCase');

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(Nullable::class, $restored);
        $this->assertSame('MixedCase', $restored->unwrap()?->value);
        $this->assertTrue($original->equals($restored));
    }

    // Tampered payloads: a crafted serialized string bypassing the constructor must not
    // silently produce an invalid instance -- __unserialize() must revalidate.

    #[Test]
    public function testTamperedIntegerPayloadIsRejected(): void
    {
        // Given a validly-serialized PositiveInt with its stored value swapped negative.
        $serialized = \serialize(new PositiveInt(5));
        $tampered = \str_replace('i:5;', 'i:-5;', $serialized);

        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage('PositiveInt type must be > 0, got -5');

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testTamperedFloatPayloadIsRejected(): void
    {
        // Given a validly-serialized PositiveFloat with its stored value swapped negative.
        $serialized = \serialize(new PositiveFloat(1.5));
        $tampered = \str_replace('d:1.5;', 'd:-1.5;', $serialized);

        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage('PositiveFloat type must be > 0, got -1.5');

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testTamperedDateTimePayloadIsRejected(): void
    {
        // Given a validly-serialized DateString with its stored value swapped to a
        // calendar-invalid date -- a shape only DateString's own manual constructor check
        // (not a constraint attribute) rejects.
        $serialized = \serialize(new DateString('2024-01-01'));
        $tampered = \str_replace('2024-01-01', '2024-02-30', $serialized);

        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage('DateString type must be a valid YYYY-MM-DD date, got 2024-02-30');

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testTamperedArrayPayloadIsRejected(): void
    {
        // Given a validly-serialized NonemptyArray with its elements swapped to empty --
        // a shape only the #[Nonempty] constraint attribute rejects.
        $serialized = \serialize(new NonemptyArray([1, 2, 3]));
        $tampered = \str_replace('a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}', 'a:0:{}', $serialized);

        // Then
        $this->expectException(StrongTypeException::class);

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testTamperedBoolPayloadViolatingTheNarrowedLiteralTypeIsRejected(): void
    {
        // Given a validly-serialized TrueValue with its stored value flipped to false --
        // a shape TrueValue's constructor `true $value` parameter type cannot accept, so
        // it surfaces as TypeError rather than a StrongType exception (see BoolType's own
        // tryFrom() doc for the same TypeError precedent).
        $serialized = \serialize(new TrueValue(true));
        $tampered = \str_replace('b:1;', 'b:0;', $serialized);

        // Then
        $this->expectException(\TypeError::class);

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testTamperedStringPayloadViolatingAManualConstructorCheckIsRejected(): void
    {
        // Given a validly-serialized EmptyString with its stored value swapped non-empty --
        // a shape only EmptyString's own manual constructor check (not a constraint
        // attribute) rejects, confirming __unserialize() re-runs subclass logic, not just
        // the base's ConstraintValidator call.
        $serialized = \serialize(new EmptyString(''));
        $tampered = \str_replace('s:0:"";', 's:5:"oops!";', $serialized);

        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage('EmptyString type must be empty, got oops!');

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testTamperedFixedSizeArrayPayloadWithMismatchedSizeIsRejected(): void
    {
        // Given a validly-serialized FixedSizeArray with its declared size changed to no
        // longer match the element count.
        $serialized = \serialize(new FixedSizeArray([1, 2, 3], 3));
        $tampered = \str_replace('s:4:"size";i:3;', 's:4:"size";i:4;', $serialized);

        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage('FixedSizeArray type must have exactly 4 elements, got 3');

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testTamperedNullablePayloadViolatingTheWrappedTypesConstraintIsRejected(): void
    {
        // Given a validly-serialized Nullable(PositiveInt, 5) with the wrapped value
        // swapped negative -- PositiveInt's own #[Positive] constraint must still catch it
        // when Nullable's __unserialize() reconstructs the nested wrapped object.
        $serialized = \serialize(new Nullable(PositiveInt::class, 5));
        $tampered = \str_replace('i:5;', 'i:-5;', $serialized);

        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage('PositiveInt type must be > 0, got -5');

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testTamperedNullablePayloadWithMismatchedTypeAndWrappedObjectIsRejected(): void
    {
        // Given a payload claiming to wrap a PositiveInt but whose nested object is
        // actually a NonemptyString -- a crafted payload could otherwise pair an
        // attacker-chosen 'type' string with an unrelated already-valid wrapped object.
        $type = PositiveInt::class;
        $mismatchedWrappedObject = \serialize(new NonemptyString('hello'));
        $tampered = \sprintf(
            'O:19:"StrongType\Nullable":2:{s:4:"type";s:%d:"%s";s:10:"strongType";%s}',
            \strlen($type),
            $type,
            $mismatchedWrappedObject
        );

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nullable wrapped value must be an instance of StrongType\Int\PositiveInt, got StrongType\String\NonemptyString');

        // When
        \unserialize($tampered);
    }

    // Wrapped types without a safe-serialization contract of their own.

    #[Test]
    public function testNullableSerializeRejectsAWrappedTypeWithoutSafeSerialization(): void
    {
        // Given a wrapped type that is otherwise perfectly usable through Nullable
        // (construction, equals(), jsonSerialize()) but implements neither __serialize()
        // nor __unserialize() itself.
        $nullable = new Nullable(UnsafeNegativeRejectingType::class, 5);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nullable cannot safely serialize a wrapped');
        $this->expectExceptionMessage('does not implement __serialize()/__unserialize()');

        // When
        \serialize($nullable);
    }

    #[Test]
    public function testTamperedNullablePayloadWrappingAnUnsafeTypeIsRejectedRatherThanSilentlyAccepted(): void
    {
        // Given a hand-crafted payload -- bypassing Nullable::__serialize()'s own guard
        // entirely, the way a real attacker would -- wrapping an UnsafeNegativeRejectingType
        // instance whose value was tampered negative after being validly serialized. Because
        // that type has no __serialize()/__unserialize() of its own, native unserialize()
        // would normally write -5 straight into its private property with no constructor
        // call at all, so its own constructor check ("must be >= 0") never gets a chance to
        // run.
        $type = UnsafeNegativeRejectingType::class;
        $validWrappedObject = \serialize(new UnsafeNegativeRejectingType(5));
        $tamperedWrappedObject = \str_replace('i:5;', 'i:-5;', $validWrappedObject);
        $tampered = \sprintf(
            'O:19:"StrongType\Nullable":2:{s:4:"type";s:%d:"%s";s:10:"strongType";%s}',
            \strlen($type),
            $type,
            $tamperedWrappedObject
        );

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('does not implement __serialize()/__unserialize()');

        // When
        \unserialize($tampered);
    }

    // Subclasses with unhandled extra constructor state.

    #[Test]
    public function testSerializingASubclassWithUnhandledExtraConstructorStateIsRejected(): void
    {
        // Given a subclass that adds constructor state (scale) beyond Integer's own value
        // without overriding __serialize()/__unserialize() to carry it. Left unguarded,
        // this either silently drops `scale` on the round trip or crashes __unserialize()'s
        // $this->__construct() call with \ArgumentCountError for a required extra
        // parameter -- both only discovered the moment someone actually serializes an
        // affected instance.
        $scaled = new ScaledIntMissingSerializationOverride(5, 9);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('declares additional constructor state (scale)');

        // When
        \serialize($scaled);
    }

    #[Test]
    public function testSerializingAGrandchildWithInheritedPrivateStateFromAnIntermediateAncestorIsRejected(): void
    {
        // Given a class two levels below Integer: the intermediate ancestor declares
        // `scale` as a *private* property and the grandchild adds nothing of its own.
        // ReflectionClass::getProperties() called only on the grandchild's own runtime
        // class would miss `scale` entirely -- a parent's private property is not visible
        // from a more distant descendant's reflection, even though it's still part of the
        // object.
        $scaled = new PrivatelyScaledIntGrandchild(5, 9);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('declares additional constructor state (scale)');

        // When
        \serialize($scaled);
    }

    // Subclasses whose constructor is overridden without certifying it's safe to inherit.

    #[Test]
    public function testSerializingASubclassWhoseConstructorTransformsItsInputIsRejected(): void
    {
        // Given a subclass whose constructor scales its input by 100 before storing it in
        // Integer's own `value` property -- no extra property, so only the separate
        // constructor-fidelity check (not the property-hierarchy check) can catch this.
        $scaled = new TransformingIntMissingSerializationOverride(5);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage("hasn't certified that it stores its argument verbatim");

        // When
        \serialize($scaled);
    }

    /**
     * Regression test: Timestamp certifies that *its own* constructor (validation-only)
     * stores its argument verbatim. ScaledTimestampMissingItsOwnCertification overrides
     * Timestamp's constructor to scale the value instead, without overriding the
     * certification method itself -- so it inherits Timestamp's certification for a
     * constructor Timestamp never saw. Before this fix, the certification check compared
     * only the boolean, not which class declared it, so this transforming override slipped
     * through and would have reconstructed 50000 from a serialized 500.
     */
    #[Test]
    public function testSerializingASubclassInheritingAnAncestorsCertificationForADifferentConstructorIsRejected(): void
    {
        // Given
        $scaled = new ScaledTimestampMissingItsOwnCertification(5);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage("hasn't certified that it stores its argument verbatim");

        // When
        \serialize($scaled);
    }

    #[Test]
    public function testSerializingASubclassRequiringAnExtraUnstoredConstructorArgumentIsRejected(): void
    {
        // Given a subclass whose constructor requires a second argument that is never
        // stored anywhere -- inherited __unserialize() only ever supplies one.
        $withUnit = new RequiresExtraUnstoredArgIntMissingSerializationOverride(5, 'cm');

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('requires more arguments than inherited __unserialize() supplies');

        // When
        \serialize($withUnit);
    }

    /**
     * Regression test: a private constructor gated behind a public static factory can
     * truthfully certify constructorPreservesSerializedStateVerbatim() -- it really does
     * only validate and store its argument verbatim -- and pass every other check
     * assertConstructorIsSafeToInherit() runs. Before this fix, nothing checked visibility,
     * so serialization would succeed and then Integer::__unserialize() would fail with
     * "Call to private method ...::__construct() from scope StrongType\Int\Integer" the
     * first time a serialized instance was actually unserialized -- caught here instead, at
     * serialize() time, with a clear configuration error.
     */
    #[Test]
    public function testSerializingASubclassWithAPrivateConstructorIsRejectedDespiteValidCertification(): void
    {
        // Given
        $value = PrivateConstructorIntMissingSerializationOverride::of(5);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage("'s constructor is private");

        // When
        \serialize($value);
    }

    /**
     * Positive control for the test above: an identical constructor override, but
     * `protected` instead of `private`, round-trips successfully. Locks in that
     * assertConstructorIsSafeToInherit()'s new visibility check doesn't overreject --
     * protected really is safe, since inherited __unserialize() calls $this->__construct()
     * from Integer's own scope, an ancestor of the declaring class, which is exactly the
     * relationship PHP's protected-access rule permits.
     */
    #[Test]
    public function testSerializingASubclassWithACertifiedProtectedConstructorRoundTrips(): void
    {
        // Given
        $value = ProtectedConstructorCertifiedInt::of(5);

        // When
        $roundTripped = \unserialize(\serialize($value));

        // Then
        $this->assertInstanceOf(ProtectedConstructorCertifiedInt::class, $roundTripped);
        $this->assertSame(5, $roundTripped->getValue());
    }

    // Wrapped types that also implement the legacy \Serializable interface.

    #[Test]
    public function testNullableSerializeRejectsAWrappedTypeThatAlsoImplementsTheLegacySerializableInterface(): void
    {
        // Given a wrapped type with safe __serialize()/__unserialize() hooks that ALSO
        // implements the legacy \Serializable interface -- a crafted payload can select
        // the unsafe legacy pair by its serialized string's format sigil alone, regardless
        // of the safe modern hooks sitting right next to it.
        $nullable = new Nullable(SafeHooksButAlsoLegacySerializableType::class, 5);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('implements the legacy \Serializable interface');

        // When
        \serialize($nullable);
    }

    #[Test]
    public function testTamperedNullablePayloadSelectingTheLegacySerializableFormatIsRejected(): void
    {
        // Given a hand-crafted payload using the legacy 'C:' sigil for the wrapped object --
        // selecting Serializable::unserialize() (which writes -5 straight into the property,
        // bypassing the constructor's ">= 0" check entirely) regardless of
        // SafeHooksButAlsoLegacySerializableType's own safe __serialize()/__unserialize().
        $type = SafeHooksButAlsoLegacySerializableType::class;
        $legacyWrappedObject = 'C:' . \strlen($type) . ':"' . $type . '":2:{-5}';
        $tampered = \sprintf(
            'O:19:"StrongType\Nullable":2:{s:4:"type";s:%d:"%s";s:10:"strongType";%s}',
            \strlen($type),
            $type,
            $legacyWrappedObject
        );

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('implements the legacy \Serializable interface');

        // When
        \unserialize($tampered);
    }

    // Nullable subclasses with unhandled extra constructor state.

    #[Test]
    public function testSerializingANullableWithUnhandledExtraConstructorStateIsRejected(): void
    {
        // Given a Nullable subclass that adds a property beyond `type`/`strongType`
        // without overriding __serialize()/__unserialize() to carry it.
        $labeled = new NullableWithUnhandledExtraState(PositiveInt::class, 5, 'primary');

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('must override __serialize() itself');

        // When
        \serialize($labeled);
    }

    // Nullable subclasses that inherit __unserialize() unchanged.

    /**
     * Regression test: a crafted payload naming a Nullable subclass whose constructor
     * forbids null must not be able to produce a subclass instance holding null anyway.
     * Before this fix, Nullable's inherited __unserialize() assigned type/strongType
     * directly without ever invoking the subclass constructor, so the subclass's own
     * "no null" invariant was never given a chance to run.
     */
    #[Test]
    public function testUnserializingAPayloadNamingANullableSubclassIsRejectedRatherThanBypassingItsInvariants(): void
    {
        // Given a validly-serialized base Nullable holding null, retargeted at a subclass
        // (NullForbiddingNullableSubclass) whose own constructor would have refused null.
        $serialized = \serialize(new Nullable(PositiveInt::class, null));
        $tampered = \str_replace(
            'O:19:"StrongType\Nullable"',
            'O:' . \strlen(NullForbiddingNullableSubclass::class) . ':"' . NullForbiddingNullableSubclass::class . '"',
            $serialized
        );

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('must override __unserialize() itself');

        // When
        \unserialize($tampered);
    }

    #[Test]
    public function testSerializingANullableSubclassThatAlsoImplementsLegacySerializableIsRejected(): void
    {
        // Given
        $withLegacyInterface = new NullableAlsoImplementingLegacySerializable(PositiveInt::class, 5);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('must not implement the legacy \Serializable interface');

        // When
        \serialize($withLegacyInterface);
    }

    /**
     * Regression test: a Nullable subclass that follows the documented remedy -- overriding
     * both hooks itself and delegating to parent::__serialize()/parent::__unserialize() --
     * must actually round-trip successfully, including its own extra property. An earlier,
     * broken version of assertNotInheritingUnsafeSerialization() checked `static::class !==
     * self::class`, which late static binding keeps true for this subclass even *inside*
     * the delegated-to parent call, so it rejected exactly this recommended pattern and made
     * every Nullable subclass unserializable regardless of what it overrode.
     */
    #[Test]
    public function testProperlyOverridingNullableSubclassRoundTripsIncludingItsOwnState(): void
    {
        // Given
        $original = new ProperlyOverridingNullableSubclass(PositiveInt::class, 5, 'primary');

        // When
        $restored = \unserialize(\serialize($original));

        // Then
        $this->assertInstanceOf(ProperlyOverridingNullableSubclass::class, $restored);
        $this->assertSame('primary', $restored->label);
        $this->assertTrue($original->equals($restored));
    }

    /**
     * Regression test: a class two levels below Nullable, where only the *intermediate*
     * ancestor overrides __serialize()/__unserialize(). That override was written before
     * this grandchild -- and its extra `tag` property -- existed, so it cannot be trusted
     * to carry it, the same gap SerializationStateGuard closes for the six value-type bases.
     */
    #[Test]
    public function testSerializingANullableGrandchildWithStateUnhandledByAnIntermediateOverrideIsRejected(): void
    {
        // Given
        $tagged = new NullableGrandchildWithUnhandledExtraState(PositiveInt::class, 5, 'primary', 'urgent');

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('declares additional state (tag)');

        // When
        \serialize($tagged);
    }

    /**
     * Regression test: a class two levels below Nullable, where only the *intermediate*
     * ancestor overrides __serialize()/__unserialize(), and this grandchild adds no new
     * property at all -- only its own constructor validation (forbidding null). Before this
     * fix, assertNotInheritingUnsafeSerialization() checked only the property hierarchy, so
     * a validation-only override like this slipped through: a crafted payload retargeting
     * an intermediate ancestor's validly-serialized null payload at this grandchild's class
     * name would have produced an instance holding null, bypassing the very constructor
     * check this class exists to enforce.
     */
    #[Test]
    public function testSerializingANullableGrandchildWithConstructorValidationUnhandledByAnIntermediateOverrideIsRejected(): void
    {
        // Given
        $nonNull = new NullableGrandchildWithUnhandledConstructorValidation(PositiveInt::class, 5);

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('overrides the constructor below');

        // When
        \serialize($nonNull);
    }
}
