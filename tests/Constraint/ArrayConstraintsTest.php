<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\ElementType;
use StrongType\Constraint\ExactCount;
use StrongType\Constraint\IsEmpty;
use StrongType\Constraint\MaxCount;
use StrongType\Constraint\MinCount;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\Not;
use StrongType\Constraint\Unique;
use StrongType\Exception\ConstraintEvaluationException;
use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Nonempty] readonly class NonemptyTestArr extends ArrayType
{
}
#[IsEmpty] readonly class IsEmptyTestArr extends ArrayType
{
}
#[MinCount(2)] readonly class MinCountTestArr extends ArrayType
{
}
#[MaxCount(3)] readonly class MaxCountTestArr extends ArrayType
{
}
#[ExactCount(2)] readonly class ExactCountTestArr extends ArrayType
{
}
#[Unique] readonly class UniqueTestArr extends ArrayType
{
}
#[Not(new Unique())] readonly class NotUniqueTestArr extends ArrayType
{
}
#[ElementType('int')] readonly class IntElementTestArr extends ArrayType
{
}
#[ElementType('string')] readonly class StringElementTestArr extends ArrayType
{
}
#[ElementType('array')] readonly class ArrayElementTestArr extends ArrayType
{
}

class ArrayConstraintsTest extends \PHPUnit\Framework\TestCase
{
    // Nonempty
    #[Test]
    public function testNonemptyArrayAccepts()
    {
        $arr = new NonemptyTestArr([1, 2, 3]);
        $this->assertSame([1, 2, 3], $arr->values);
    }

    #[Test]
    public function testNonemptyArrayRejects()
    {
        $this->expectException(StrongTypeException::class);
        new NonemptyTestArr([]);
    }

    // IsEmpty
    #[Test]
    public function testIsEmptyAccepts()
    {
        $arr = new IsEmptyTestArr([]);
        $this->assertSame([], $arr->values);
    }

    #[Test]
    public function testIsEmptyRejects()
    {
        $this->expectException(StrongTypeException::class);
        new IsEmptyTestArr([1]);
    }

    #[Test]
    #[DataProvider('dataProviderForIsEmptyElementCountMessages')]
    public function testIsEmptyRejectionMessageReportsElementCount(array $value, string $expectedMessage)
    {
        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage($expectedMessage);

        // When
        new IsEmptyTestArr($value);
    }

    public static function dataProviderForIsEmptyElementCountMessages(): array
    {
        return [
            'singular' => [[1], 'got an array with 1 element(s)'],
            'plural'   => [[1, 2], 'got an array with 2 element(s)'],
        ];
    }

    #[Test]
    public function testIsEmptyRejectionWithHostileElementIsCatchable()
    {
        // Given
        // An element whose __debugInfo() throws: the old print_r-based message
        // would turn this rejection into an uncatchable warning. The bounded,
        // print_r-free message must keep it a catchable StrongTypeException.
        $hostile = new class {
            public function __debugInfo(): array
            {
                throw new \RuntimeException('boom');
            }
        };

        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new IsEmptyTestArr([$hostile]);
    }

    // MinCount
    #[Test]
    public function testMinCountAccepts()
    {
        $arr = new MinCountTestArr([1, 2]);
        $this->assertSame([1, 2], $arr->values);
    }

    #[Test]
    public function testMinCountRejects()
    {
        $this->expectException(StrongTypeException::class);
        new MinCountTestArr([1]);
    }

    // MaxCount
    #[Test]
    public function testMaxCountAccepts()
    {
        $arr = new MaxCountTestArr([1, 2, 3]);
        $this->assertSame([1, 2, 3], $arr->values);
    }

    #[Test]
    public function testMaxCountRejects()
    {
        $this->expectException(StrongTypeException::class);
        new MaxCountTestArr([1, 2, 3, 4]);
    }

    // ExactCount
    #[Test]
    public function testExactCountAccepts()
    {
        $arr = new ExactCountTestArr([1, 2]);
        $this->assertSame([1, 2], $arr->values);
    }

    #[Test]
    public function testExactCountRejectsTooFew()
    {
        $this->expectException(StrongTypeException::class);
        new ExactCountTestArr([1]);
    }

    #[Test]
    public function testExactCountRejectsTooMany()
    {
        $this->expectException(StrongTypeException::class);
        new ExactCountTestArr([1, 2, 3]);
    }

    // Unique
    #[Test]
    public function testUniqueAccepts()
    {
        $arr = new UniqueTestArr([1, 2, 3]);
        $this->assertSame([1, 2, 3], $arr->values);
    }

    #[Test]
    public function testUniqueRejectsDuplicates()
    {
        $this->expectException(StrongTypeException::class);
        new UniqueTestArr([1, 1, 2]);
    }

    #[Test]
    public function testUniqueAcceptsLooselyEqualButStrictlyDistinctValues()
    {
        // Strict equality: 0 !== '0', 1 !== '1', true !== 1.
        // SORT_REGULAR-based array_unique would have collapsed these.
        $arr = new UniqueTestArr([0, '0', 1, '1', true]);
        $this->assertSame([0, '0', 1, '1', true], $arr->values);
    }

    #[Test]
    public function testUniqueRejectsDuplicateNan()
    {
        // NAN === NAN is false in PHP, but two NaNs are indistinguishable as
        // values; Unique treats them as duplicates.
        $this->expectException(StrongTypeException::class);
        new UniqueTestArr([\NAN, \NAN]);
    }

    #[Test]
    public function testUniqueRejectsWhenComparisonIsIndeterminateDueToCircularReference()
    {
        // Given two distinct self-referential array elements: native === cannot determine
        // whether they are duplicates without risking unbounded recursion.
        $innerA = [];
        $innerA[] = &$innerA;
        $innerB = [];
        $innerB[] = &$innerB;

        // Then -- Unique rejects rather than silently treating an unprovable pair as distinct.
        $this->expectException(StrongTypeException::class);

        // When
        new UniqueTestArr([$innerA, $innerB]);
    }

    /**
     * Regression test: Unique throws ConstraintEvaluationException (rather than returning a
     * normal violation string) for the indeterminate-cyclic-comparison case specifically so
     * a #[Not(new Unique)] composition can't invert "couldn't tell whether these are
     * duplicates" into "so they must be duplicates, which is exactly what I wanted". Before
     * this fix, the indeterminate case returned an ordinary violation string, which Not read
     * as "Unique failed (found something wrong)" and inverted into an accept -- silently
     * admitting a value neither side had actually confirmed contained a duplicate.
     */
    #[Test]
    public function testNotUniqueDoesNotInvertAnIndeterminateComparisonIntoAnAccept()
    {
        // Given two distinct self-referential array elements: native === cannot determine
        // whether they are duplicates without risking unbounded recursion.
        $innerA = [];
        $innerA[] = &$innerA;
        $innerB = [];
        $innerB[] = &$innerB;

        // Then
        $this->expectException(ConstraintEvaluationException::class);

        // When
        new NotUniqueTestArr([$innerA, $innerB]);
    }

    // ElementType
    #[Test]
    public function testElementTypeIntAccepts()
    {
        $arr = new IntElementTestArr([1, 2, 3]);
        $this->assertSame([1, 2, 3], $arr->values);
    }

    #[Test]
    public function testElementTypeIntRejects()
    {
        $this->expectException(StrongTypeException::class);
        new IntElementTestArr([1, 'two', 3]);
    }

    #[Test]
    public function testElementTypeStringAccepts()
    {
        $arr = new StringElementTestArr(['a', 'b']);
        $this->assertSame(['a', 'b'], $arr->values);
    }

    #[Test]
    public function testElementTypeStringRejects()
    {
        $this->expectException(StrongTypeException::class);
        new StringElementTestArr(['a', 1]);
    }

    #[Test]
    public function testElementTypeEmptyArrayPasses()
    {
        $arr = new IntElementTestArr([]);
        $this->assertSame([], $arr->values);
    }

    #[Test]
    public function testElementTypeIsShallowAndDoesNotRecurseIntoNestedArrays()
    {
        // Given: ElementType validates only the direct (top-level) children.
        // It does not recurse — mixed-type contents inside nested arrays are
        // not inspected. To validate nested element types, wrap the inner
        // arrays in their own strong type.
        $mixedNestedContents = [[1, 'two', 3.0], ['a', null, true], []];

        // When
        $arr = new ArrayElementTestArr($mixedNestedContents);

        // Then
        $this->assertSame($mixedNestedContents, $arr->values);
    }

    #[Test]
    public function testExactCountPriorityIsSizeBand(): void
    {
        // Given
        $constraint = new ExactCount(2);

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(50, $priority);
    }

    #[Test]
    public function testMinCountPriorityIsSizeBand(): void
    {
        // Given
        $constraint = new MinCount(2);

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(50, $priority);
    }

    #[Test]
    public function testMaxCountPriorityIsSizeBand(): void
    {
        // Given
        $constraint = new MaxCount(3);

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(50, $priority);
    }

    #[Test]
    public function testIsEmptyPriorityIsSizeBand(): void
    {
        // Given
        $constraint = new IsEmpty();

        // When
        $priority = $constraint->priority();

        // Then
        $this->assertSame(50, $priority);
    }

    #[Test]
    public function testUniqueReturnsNullForNonArray(): void
    {
        // Given
        $constraint = new Unique();

        // When
        $result = $constraint->validate('not an array', 'X');

        // Then
        $this->assertNull($result);
    }

    #[Test]
    public function testElementTypeReturnsNullForNonArray(): void
    {
        // Given
        $constraint = new ElementType('int');

        // When
        $result = $constraint->validate('not an array', 'X');

        // Then
        $this->assertNull($result);
    }
}
