<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Arrays\ArrayType;
use StrongType\Bool\BoolType;
use StrongType\Constraint\AllOf;
use StrongType\Constraint\AnyOf;
use StrongType\Constraint\ConstraintInterface;
use StrongType\Constraint\Even;
use StrongType\Constraint\Finite;
use StrongType\Constraint\InEnum;
use StrongType\Constraint\InList;
use StrongType\Constraint\IsList;
use StrongType\Constraint\InRange;
use StrongType\Constraint\Max;
use StrongType\Constraint\Min;
use StrongType\Constraint\MinLength;
use StrongType\Constraint\Negative;
use StrongType\Constraint\Nonnegative;
use StrongType\Constraint\Nonpositive;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\Nonzero;
use StrongType\Constraint\Not;
use StrongType\Constraint\Pattern;
use StrongType\Constraint\Positive;
use StrongType\Constraint\ValueType;
use StrongType\DateTime\DateTime;
use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Exception\StrongTypeException;
use StrongType\Float\FloatingPoint;
use StrongType\Int\Integer;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

enum ApplicabilityIntEnum: int
{
    case One = 1;
    case Two = 2;
}

enum ApplicabilityStringEnum: string
{
    case Red  = 'red';
    case Blue = 'blue';
}

enum ApplicabilityPureEnum
{
    case Alpha;
    case Beta;
}

enum ApplicabilityEmptyEnum
{
}

// --- Misapplied constraints: each can never engage on the base it is attached to. ---

#[Positive]
readonly class NumericOnStringType extends StringType
{
}

#[Even]
readonly class IntegerOnFloatType extends FloatingPoint
{
}

#[Finite]
readonly class FloatOnIntegerType extends Integer
{
}

#[MinLength(3)]
readonly class StringOnIntegerType extends Integer
{
}

#[IsList]
readonly class ArrayOnStringType extends StringType
{
}

#[Nonempty]
readonly class StringOrArrayOnIntegerType extends Integer
{
}

#[Positive]
readonly class NumericOnDateTimeType extends DateTime
{
}

#[MinLength(1)]
readonly class StringOnBoolType extends BoolType
{
}

#[Positive]
readonly class NumericOnArrayType extends ArrayType
{
}

// --- Instance-specific derivation: InList / InEnum operand domains. ---

#[InList(1, 2)]
readonly class IntListOnStringType extends StringType
{
}

#[InList('red', 'blue')]
readonly class StringListOnIntegerType extends Integer
{
}

#[InEnum(ApplicabilityIntEnum::class)]
readonly class IntEnumOnStringType extends StringType
{
}

#[InEnum(ApplicabilityStringEnum::class)]
readonly class StringEnumOnIntegerType extends Integer
{
}

#[InEnum(ApplicabilityPureEnum::class)]
readonly class PureEnumOnIntegerType extends Integer
{
}

#[InList(1, 2)]
readonly class IntListOnIntegerType extends Integer
{
}

#[InList('red', 'blue')]
readonly class StringListOnStringType extends StringType
{
}

#[InList(1, 'red')]
readonly class MixedListOnStringType extends StringType
{
}

#[InEnum(ApplicabilityIntEnum::class)]
readonly class IntEnumOnIntegerType extends Integer
{
}

#[InEnum(ApplicabilityStringEnum::class)]
readonly class StringEnumOnStringType extends StringType
{
}

#[InEnum(ApplicabilityPureEnum::class)]
readonly class PureEnumOnStringType extends StringType
{
}

// --- Combinators: intersection of the children's domains. ---

#[AnyOf(new Min(0), new Pattern('/x/'))]
readonly class EmptyIntersectionAnyOfType extends Integer
{
}

#[AllOf(new Min(0), new Pattern('/x/'))]
readonly class EmptyIntersectionAllOfType extends StringType
{
}

#[AnyOf(new Min(0), new Even())]
readonly class VacuousAnyOfOnStringType extends StringType
{
}

#[Not(new Positive())]
readonly class DeadNotOnStringType extends StringType
{
}

#[AnyOf(new Even(), new Min(100))]
readonly class NarrowedAnyOfOnIntegerType extends Integer
{
}

#[Not(new Pattern('/\d/'))]
readonly class LiveNotOnStringType extends StringType
{
}

// Nested combinator whose intersection narrows to [Int] but sits on a string base.
#[AnyOf(new Not(new AllOf(new Even(), new Min(0))), new Min(5))]
readonly class NestedCombinatorOnStringType extends StringType
{
}

// A well-formed parent with a mis-defined child: constraints resolve per class, so the
// parent must stay usable while the child is rejected.
#[Pattern('/\A[a-z]+\z/')]
readonly class ApplicableParentString extends StringType
{
}

#[Positive]
readonly class MisappliedChildString extends ApplicableParentString
{
}

// The reverse: the mis-defined constraint sits on the parent, so the child inherits it.
#[Nonzero]
readonly class InheritedMisapplicationParent extends StringType
{
}

readonly class InheritedMisapplicationChild extends InheritedMisapplicationParent
{
}

// A user-defined base (not one of the six built-in bases) that declares its own VALUE_TYPE
// constant with the wrong type -- present, but not a ValueType case. This must fail loudly
// rather than silently skip the applicability check, the way a genuinely absent constant does.
readonly class MalformedValueTypeType implements \JsonSerializable, \Stringable, \StrongType\HasEquals
{
    public const string VALUE_TYPE = 'not-a-value-type';

    public function __construct(public string $value)
    {
        \StrongType\Constraint\ConstraintValidator::validate($this, $this->value);
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

    #[\Override]
    public function equals(\StrongType\HasEquals $other): bool
    {
        return $other instanceof self && $other->value === $this->value;
    }
}

class ApplicabilityTest extends \PHPUnit\Framework\TestCase
{
    // --- Base value types ---

    #[Test]
    #[DataProvider('dataProviderForBaseValueTypes')]
    public function testBaseDeclaresItsValueType(string $baseClass, ValueType $expected)
    {
        // Given / When
        $valueType = $baseClass::VALUE_TYPE;

        // Then
        $this->assertSame($expected, $valueType);
    }

    public static function dataProviderForBaseValueTypes(): array
    {
        return [
            'Integer'       => [Integer::class, ValueType::Int],
            'FloatingPoint' => [FloatingPoint::class, ValueType::Float],
            'StringType'    => [StringType::class, ValueType::String],
            'BoolType'      => [BoolType::class, ValueType::Bool],
            'DateTime'      => [DateTime::class, ValueType::String],
            'ArrayType'     => [ArrayType::class, ValueType::Array],
        ];
    }

    #[Test]
    public function testSubclassInheritsItsBaseValueType()
    {
        // Given / When / Then
        $this->assertSame(ValueType::Int, \StrongType\Int\PositiveInt::VALUE_TYPE);
        $this->assertSame(ValueType::String, \StrongType\DateTime\DateString::VALUE_TYPE);
        $this->assertSame(ValueType::Array, \StrongType\Arrays\ListArray::VALUE_TYPE);
    }

    // --- Misapplied constraints are definition-time errors ---

    #[Test]
    #[DataProvider('dataProviderForMisappliedConstraints')]
    public function testMisappliedConstraintIsADefinitionError(callable $construct, string $expectedMessage)
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);

        // When
        $construct();
    }

    public static function dataProviderForMisappliedConstraints(): array
    {
        return [
            'numeric constraint on a string type' => [
                static fn(): object => new NumericOnStringType('abc'),
                'Positive is not applicable to String values',
            ],
            'integer-only constraint on a float type' => [
                static fn(): object => new IntegerOnFloatType(1.5),
                'Even is not applicable to Float values',
            ],
            'float-only constraint on an integer type' => [
                static fn(): object => new FloatOnIntegerType(1),
                'Finite is not applicable to Int values',
            ],
            'string constraint on an integer type' => [
                static fn(): object => new StringOnIntegerType(1),
                'MinLength is not applicable to Int values',
            ],
            'array constraint on a string type' => [
                static fn(): object => new ArrayOnStringType('abc'),
                'IsList is not applicable to String values',
            ],
            'string-or-array constraint on an integer type' => [
                static fn(): object => new StringOrArrayOnIntegerType(1),
                'Nonempty is not applicable to Int values',
            ],
            'numeric constraint on a datetime type' => [
                static fn(): object => new NumericOnDateTimeType('2024-01-01'),
                'Positive is not applicable to String values',
            ],
            'string constraint on a bool type' => [
                static fn(): object => new StringOnBoolType(true),
                'MinLength is not applicable to Bool values',
            ],
            'numeric constraint on an array type' => [
                static fn(): object => new NumericOnArrayType([1, 2]),
                'Positive is not applicable to Array values',
            ],
            'int-operand InList on a string type' => [
                static fn(): object => new IntListOnStringType('red'),
                'InList is not applicable to String values',
            ],
            'string-operand InList on an integer type' => [
                static fn(): object => new StringListOnIntegerType(1),
                'InList is not applicable to Int values',
            ],
            'int-backed InEnum on a string type' => [
                static fn(): object => new IntEnumOnStringType('red'),
                'InEnum is not applicable to String values',
            ],
            'string-backed InEnum on an integer type' => [
                static fn(): object => new StringEnumOnIntegerType(1),
                'InEnum is not applicable to Int values',
            ],
            'pure InEnum on an integer type' => [
                static fn(): object => new PureEnumOnIntegerType(1),
                'InEnum is not applicable to Int values',
            ],
            'AnyOf whose children share no value type' => [
                static fn(): object => new EmptyIntersectionAnyOfType(1),
                'AnyOf is not applicable to Int values on',
            ],
            'AllOf whose children share no value type' => [
                static fn(): object => new EmptyIntersectionAllOfType('abc'),
                'AllOf is not applicable to String values on',
            ],
            'AnyOf with a child that cannot engage on the base' => [
                static fn(): object => new VacuousAnyOfOnStringType('abc'),
                'AnyOf is not applicable to String values',
            ],
            'Not whose child cannot engage on the base' => [
                static fn(): object => new DeadNotOnStringType('abc'),
                'Not is not applicable to String values',
            ],
        ];
    }

    #[Test]
    public function testErrorMessageNamesTheClassAndTheAcceptedTypes()
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage(
            'Positive is not applicable to String values on ' . NumericOnStringType::class
            . '; it applies to [Int, Float]'
        );

        // When
        new NumericOnStringType('abc');
    }

    #[Test]
    public function testMalformedValueTypeConstantThrowsInsteadOfSilentlySkipping()
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage(
            MalformedValueTypeType::class . '::VALUE_TYPE must be a ' . ValueType::class . ' case, got string'
        );

        // When
        new MalformedValueTypeType('abc');
    }

    #[Test]
    public function testEmptyIntersectionCombinatorReportsThatItAppliesNowhere()
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('it applies to no value type');

        // When
        new EmptyIntersectionAnyOfType(1);
    }

    #[Test]
    public function testMisapplicationIsRejectedBeforeAnyValueIsValidated()
    {
        // Then -- the value is irrelevant: the type itself is mis-defined, so even a value
        // the constraint would have accepted still fails.
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new NumericOnStringType('');
    }

    #[Test]
    public function testMisapplicationIsNotAValidationFailure()
    {
        // Then -- ConstraintConfigurationException is a LogicException, deliberately
        // outside the StrongTypeException hierarchy, so catch(StrongTypeException) must
        // not swallow a mis-defined type.
        try {
            new NumericOnStringType('abc');
            $this->fail('Expected ConstraintConfigurationException');
        } catch (ConstraintConfigurationException $e) {
            // When / Then
            $this->assertNotInstanceOf(StrongTypeException::class, $e);
            $this->assertInstanceOf(\LogicException::class, $e);
        }
    }

    #[Test]
    public function testMisapplicationThrowsAgainOnEveryConstruction()
    {
        // Given -- a first construction that was rejected. The resolved list is never
        // written to the cache on the failing path, so nothing is poisoned or memoised
        // into silence.
        try {
            new NumericOnStringType('abc');
            $this->fail('Expected ConstraintConfigurationException');
        } catch (ConstraintConfigurationException) {
            // expected
        }

        // Then
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Positive is not applicable to String values');

        // When
        new NumericOnStringType('xyz');
    }

    #[Test]
    public function testAMisappliedChildDoesNotBreakItsWellFormedParent()
    {
        // Given / When -- constraints resolve per class, so the parent is unaffected.
        $parent = new ApplicableParentString('abc');

        // Then
        $this->assertSame('abc', $parent->value);

        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Positive is not applicable to String values on ' . MisappliedChildString::class);

        new MisappliedChildString('abc');
    }

    #[Test]
    public function testAnInheritedMisappliedConstraintIsRejectedOnTheChildToo()
    {
        // Then -- the check runs over the whole hierarchy walk, not just the class's own
        // attributes, so a child inherits its parent's mis-definition.
        $this->expectException(ConstraintConfigurationException::class);
        $this->expectExceptionMessage('Nonzero is not applicable to String values on ' . InheritedMisapplicationChild::class);

        // When
        new InheritedMisapplicationChild('abc');
    }

    // --- Domain-aligned declarations still resolve ---

    #[Test]
    public function testIntOperandInListOnIntegerTypeResolves()
    {
        // Given / When
        $obj = new IntListOnIntegerType(2);

        // Then
        $this->assertSame(2, $obj->value);
    }

    #[Test]
    public function testStringOperandInListOnStringTypeResolves()
    {
        // Given / When
        $obj = new StringListOnStringType('red');

        // Then
        $this->assertSame('red', $obj->value);
    }

    #[Test]
    public function testMixedOperandInListResolvesOnEitherOperandType()
    {
        // Given / When -- a list mixing int and string operands accepts both domains.
        $obj = new MixedListOnStringType('red');

        // Then
        $this->assertSame('red', $obj->value);
    }

    #[Test]
    public function testIntBackedInEnumOnIntegerTypeResolves()
    {
        // Given / When
        $obj = new IntEnumOnIntegerType(1);

        // Then
        $this->assertSame(1, $obj->value);
    }

    #[Test]
    public function testStringBackedInEnumOnStringTypeResolves()
    {
        // Given / When
        $obj = new StringEnumOnStringType('red');

        // Then
        $this->assertSame('red', $obj->value);
    }

    #[Test]
    public function testPureInEnumOnStringTypeResolves()
    {
        // Given / When -- a pure enum is compared against its case names, which are strings.
        $obj = new PureEnumOnStringType('Alpha');

        // Then
        $this->assertSame('Alpha', $obj->value);
    }

    #[Test]
    public function testNarrowedCombinatorResolvesWhenTheIntersectionCoversTheBase()
    {
        // Given / When -- [Int] ∩ [Int, Float] = [Int], which covers an Integer base.
        $obj = new NarrowedAnyOfOnIntegerType(2);

        // Then
        $this->assertSame(2, $obj->value);
    }

    #[Test]
    public function testLiveNotResolvesWhenItsChildEngagesOnTheBase()
    {
        // Given / When
        $obj = new LiveNotOnStringType('abc');

        // Then
        $this->assertSame('abc', $obj->value);
    }

    #[Test]
    public function testResolvedTypesStillValidateNormally()
    {
        // Then -- passing applicability does not weaken validation.
        $this->expectException(StrongTypeException::class);

        // When
        new IntListOnIntegerType(99);
    }

    // --- Declared applicability sets ---

    #[Test]
    #[DataProvider('dataProviderForAcceptedValueTypes')]
    public function testConstraintDeclaresItsAcceptedValueTypes(ConstraintInterface $constraint, array $expected)
    {
        // Given / When
        $accepted = $constraint->acceptedValueTypes();

        // Then
        $this->assertSame($expected, $accepted);
    }

    public static function dataProviderForAcceptedValueTypes(): array
    {
        return [
            'numeric'                  => [new Positive(), [ValueType::Int, ValueType::Float]],
            'float only'               => [new Finite(), [ValueType::Float]],
            'integer only'             => [new Even(), [ValueType::Int]],
            'string only'              => [new MinLength(1), [ValueType::String]],
            'array only'               => [new IsList(), [ValueType::Array]],
            'string or array'          => [new Nonempty(), [ValueType::String, ValueType::Array]],
            'int-operand InList'       => [new InList(1, 2), [ValueType::Int]],
            'float-operand InList'     => [new InList(1.5), [ValueType::Float]],
            'string-operand InList'    => [new InList('a'), [ValueType::String]],
            'bool-operand InList'      => [new InList(true), [ValueType::Bool]],
            'array-operand InList'     => [new InList([1]), [ValueType::Array]],
            'mixed-operand InList'     => [new InList(1, 'a'), [ValueType::Int, ValueType::String]],
            'duplicate-operand InList' => [new InList(1, 2, 3), [ValueType::Int]],
            'null-only InList'         => [new InList(null), ValueType::cases()],
            'object-only InList'       => [new InList(new \stdClass()), ValueType::cases()],
            'null+object InList'       => [new InList(null, new \stdClass()), ValueType::cases()],
            'operands beside null'     => [new InList(null, 'a'), [ValueType::String]],
            'zero int vs zero float'   => [new InList(0, 0.0), [ValueType::Int, ValueType::Float]],
            'false is a bool operand'  => [new InList(false), [ValueType::Bool]],
            'zero string operand'      => [new InList('0'), [ValueType::String]],
            'caseless InEnum'          => [new InEnum(ApplicabilityEmptyEnum::class), [ValueType::String]],
            'int-backed InEnum'        => [new InEnum(ApplicabilityIntEnum::class), [ValueType::Int]],
            'string-backed InEnum'     => [new InEnum(ApplicabilityStringEnum::class), [ValueType::String]],
            'pure InEnum'              => [new InEnum(ApplicabilityPureEnum::class), [ValueType::String]],
            'Not delegates to child'   => [new Not(new Even()), [ValueType::Int]],
            'AnyOf intersects'         => [new AnyOf(new Even(), new Min(0)), [ValueType::Int]],
            'AllOf intersects'         => [new AllOf(new Even(), new Min(0)), [ValueType::Int]],
            'AnyOf disjoint children'  => [new AnyOf(new Min(0), new Pattern('/x/')), []],
            'AllOf disjoint children'  => [new AllOf(new Min(0), new Pattern('/x/')), []],
            'nested combinators'       => [new Not(new AnyOf(new Nonempty(), new MinLength(1))), [ValueType::String]],
            'AnyOf -> Not -> AllOf'    => [
                new AnyOf(new Not(new AllOf(new Nonempty(), new MinLength(1))), new Pattern('/x/')),
                [ValueType::String],
            ],
            'nesting narrows to empty' => [
                new AnyOf(new Not(new AllOf(new Even(), new Min(0))), new Pattern('/x/')),
                [],
            ],
            'AllOf of AnyOfs'          => [
                new AllOf(new AnyOf(new Nonempty(), new MinLength(1)), new AnyOf(new Pattern('/x/'), new MinLength(2))),
                [ValueType::String],
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForOrderIndependentIntersection')]
    public function testIntersectionIsOrderedByEnumDeclarationNotByChildOrder(
        ConstraintInterface $forwards,
        ConstraintInterface $backwards,
    ) {
        // Given -- children whose OWN sets are ordered differently: InList reports its
        // operands in argument order ([Float, Int] here), Min reports [Int, Float]. If the
        // intersection inherited a child's ordering, the two directions would disagree.
        $this->assertSame([ValueType::Float, ValueType::Int], (new InList(0.0, 0))->acceptedValueTypes());
        $this->assertSame([ValueType::Int, ValueType::Float], (new Min(0))->acceptedValueTypes());

        // When
        $acceptedForwards  = $forwards->acceptedValueTypes();
        $acceptedBackwards = $backwards->acceptedValueTypes();

        // Then -- the intersection filters ValueType::cases(), so it is argument-order
        // independent and the error message is stable whichever way the children are passed.
        $this->assertSame([ValueType::Int, ValueType::Float], $acceptedForwards);
        $this->assertSame($acceptedForwards, $acceptedBackwards);
    }

    public static function dataProviderForOrderIndependentIntersection(): array
    {
        return [
            'AllOf' => [
                new AllOf(new InList(0.0, 0), new Min(0)),
                new AllOf(new Min(0), new InList(0.0, 0)),
            ],
            'AnyOf' => [
                new AnyOf(new InList(0.0, 0), new Min(0)),
                new AnyOf(new Min(0), new InList(0.0, 0)),
            ],
        ];
    }

    #[Test]
    public function testNestedCombinatorFailureReportsTheFullMessage()
    {
        // Given
        $expected = 'AnyOf is not applicable to String values on '
            . NestedCombinatorOnStringType::class . '; it applies to [Int]';

        // When
        try {
            new NestedCombinatorOnStringType('abc');
            $this->fail('Expected ConstraintConfigurationException');
        } catch (ConstraintConfigurationException $e) {
            // Then -- full equality, not a substring: the message must name the outermost
            // combinator, the base's value type, the class, and the surviving intersection,
            // with nothing else appended or prefixed.
            $this->assertSame($expected, $e->getMessage());
        }
    }

    #[Test]
    #[DataProvider('dataProviderForBaseValueTypes')]
    public function testValueTypeConstantIsFinal(string $baseClass, ValueType $expected)
    {
        // Given -- a subclass must not be able to relabel its value type; overriding
        // VALUE_TYPE would reopen exactly the fail-open hole this check closes.
        $constant = (new \ReflectionClass($baseClass))->getReflectionConstant('VALUE_TYPE');
        $this->assertNotFalse($constant);

        // When
        $isFinal = $constant->isFinal();

        // Then
        $this->assertTrue($isFinal, "{$baseClass}::VALUE_TYPE must be final");
        $this->assertSame($expected, $baseClass::VALUE_TYPE);
    }

    #[Test]
    #[DataProvider('dataProviderForNumericGuardAlignment')]
    public function testNumericConstraintGuardsMatchTheirDeclaredValueTypes(
        ConstraintInterface $constraint,
        string $rejectedUnderTheOldGuard,
    ) {
        // Given -- these constraints declare [Int, Float], so their runtime guard must not
        // engage on a string: a declared set wider than the guard would let a half-dead
        // constraint back in. Each probe is a numeric string the OLD is_numeric() guard
        // would have rejected, so the case fails if the guard is ever widened again.
        $accepted = $constraint->acceptedValueTypes();
        $this->assertSame([ValueType::Int, ValueType::Float], $accepted);
        $this->assertTrue(\is_numeric($rejectedUnderTheOldGuard));

        // When
        $errorForNumericString = $constraint->validate($rejectedUnderTheOldGuard, 'Example');

        // Then
        $this->assertNull($errorForNumericString);

        // And the same value as an int/float is still rejected, proving the probe is
        // genuinely out of bounds rather than trivially acceptable.
        $this->assertNotNull($constraint->validate((int) $rejectedUnderTheOldGuard, 'Example'));
    }

    public static function dataProviderForNumericGuardAlignment(): array
    {
        return [
            'Min'         => [new Min(1), '0'],
            'Max'         => [new Max(-1), '0'],
            'InRange'     => [new InRange(10, 20), '0'],
            'Positive'    => [new Positive(), '0'],
            'Negative'    => [new Negative(), '0'],
            'Nonnegative' => [new Nonnegative(), '-1'],
            'Nonpositive' => [new Nonpositive(), '1'],
            'Nonzero'     => [new Nonzero(), '0'],
        ];
    }

    #[Test]
    public function testNumericConstraintsStillEngageOnIntAndFloat()
    {
        // Given / When / Then -- tightening the guard must not weaken the real domains.
        $this->assertNotNull((new Positive())->validate(0, 'Example'));
        $this->assertNotNull((new Positive())->validate(0.0, 'Example'));
        $this->assertNull((new Positive())->validate(1, 'Example'));
        $this->assertNull((new Positive())->validate(0.5, 'Example'));
    }

    // --- Coverage sweeps over the library itself ---

    #[Test]
    #[DataProvider('dataProviderForBuiltInConstraints')]
    public function testEveryBuiltInConstraintDeclaresANonEmptySetUnlessItIsACombinator(string $constraintClass)
    {
        // Given -- combinators derive their set from children, so only they may be empty.
        $isCombinator = \in_array($constraintClass, [AnyOf::class, AllOf::class, Not::class], true);
        if ($isCombinator) {
            $this->assertTrue((new \ReflectionClass($constraintClass))->implementsInterface(ConstraintInterface::class));
            return;
        }

        // When
        $accepted = self::instantiate($constraintClass)->acceptedValueTypes();

        // Then
        $this->assertNotEmpty($accepted, "{$constraintClass} declares no applicable value type");
        $this->assertSame(\array_values(\array_unique($accepted, \SORT_REGULAR)), $accepted);
    }

    public static function dataProviderForBuiltInConstraints(): array
    {
        $cases = [];
        foreach (self::builtInConstraintClasses() as $class) {
            $cases[\StrongType\Util\ShortClassName::of($class)] = [$class];
        }

        return $cases;
    }

    #[Test]
    public function testEveryBuiltInTypeCarriesOnlyApplicableConstraints()
    {
        // Given -- every concrete built-in type, with the constraints it declares or inherits.
        $checked = 0;

        foreach (self::builtInTypeClasses() as $class) {
            $ref       = new \ReflectionClass($class);
            $valueType = $ref->getReflectionConstant('VALUE_TYPE');
            $this->assertNotFalse($valueType, "{$class} does not declare VALUE_TYPE");

            $classValueType = $valueType->getValue();
            $this->assertInstanceOf(ValueType::class, $classValueType);

            // When -- walk the same hierarchy the validator walks.
            $walk = $ref;
            do {
                foreach ($walk->getAttributes(ConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF) as $attr) {
                    $constraint = $attr->newInstance();

                    // Then
                    $this->assertContains(
                        $classValueType,
                        $constraint->acceptedValueTypes(),
                        $constraint::class . " is not applicable to {$classValueType->name} values on {$class}",
                    );
                    $checked++;
                }
            } while ($walk = $walk->getParentClass());
        }

        $this->assertGreaterThan(0, $checked);
    }

    /**
     * @return list<class-string<ConstraintInterface>>
     */
    private static function builtInConstraintClasses(): array
    {
        $classes = [];
        foreach (\glob(__DIR__ . '/../../src/Constraint/*.php') ?: [] as $file) {
            /** @var class-string $class */
            $class = 'StrongType\\Constraint\\' . \basename($file, '.php');
            if (!\class_exists($class)) {
                continue;
            }
            $ref = new \ReflectionClass($class);
            if ($ref->isAbstract() || !$ref->implementsInterface(ConstraintInterface::class)) {
                continue;
            }
            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * @return list<class-string>
     */
    private static function builtInTypeClasses(): array
    {
        $bases = [
            'Int'      => Integer::class,
            'Float'    => FloatingPoint::class,
            'String'   => StringType::class,
            'Bool'     => BoolType::class,
            'DateTime' => DateTime::class,
            'Arrays'   => ArrayType::class,
        ];

        $classes = [];
        foreach ($bases as $directory => $base) {
            foreach (\glob(__DIR__ . "/../../src/{$directory}/*.php") ?: [] as $file) {
                /** @var class-string $class */
                $class = 'StrongType\\' . $directory . '\\' . \basename($file, '.php');
                if (!\class_exists($class) || (new \ReflectionClass($class))->isAbstract()) {
                    continue;
                }
                if (!\is_subclass_of($class, $base)) {
                    continue;
                }
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Instantiate a constraint, supplying a minimal valid argument for the ones that
     * require constructor parameters.
     *
     * @param class-string<ConstraintInterface> $class
     */
    private static function instantiate(string $class): ConstraintInterface
    {
        $arguments = match ($class) {
            \StrongType\Constraint\DateFormat::class   => ['Y-m-d'],
            \StrongType\Constraint\DivisibleBy::class  => [2],
            \StrongType\Constraint\ElementType::class  => ['int'],
            \StrongType\Constraint\ExactCount::class   => [1],
            \StrongType\Constraint\InEnum::class       => [ApplicabilityIntEnum::class],
            \StrongType\Constraint\InList::class       => [1],
            \StrongType\Constraint\InRange::class      => [0, 1],
            \StrongType\Constraint\Max::class          => [1],
            \StrongType\Constraint\MaxCount::class     => [1],
            \StrongType\Constraint\MaxLength::class    => [1],
            \StrongType\Constraint\Min::class          => [0],
            \StrongType\Constraint\MinCount::class     => [1],
            \StrongType\Constraint\MinLength::class    => [1],
            \StrongType\Constraint\NotPattern::class   => ['/x/'],
            \StrongType\Constraint\Pattern::class      => ['/x/'],
            default                                    => [],
        };

        return new $class(...$arguments);
    }
}
