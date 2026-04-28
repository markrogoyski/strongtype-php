<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\ElementType;
use StrongType\Constraint\ExactCount;
use StrongType\Constraint\IsEmpty;
use StrongType\Constraint\MaxCount;
use StrongType\Constraint\MinCount;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\Unique;
use StrongType\Exception\StrongTypeException;
use StrongType\Arrays\ArrayType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Nonempty] class NonemptyTestArr extends ArrayType
{
}
#[IsEmpty] class IsEmptyTestArr extends ArrayType
{
}
#[MinCount(2)] class MinCountTestArr extends ArrayType
{
}
#[MaxCount(3)] class MaxCountTestArr extends ArrayType
{
}
#[ExactCount(2)] class ExactCountTestArr extends ArrayType
{
}
#[Unique] class UniqueTestArr extends ArrayType
{
}
#[ElementType('int')] class IntElementTestArr extends ArrayType
{
}
#[ElementType('string')] class StringElementTestArr extends ArrayType
{
}
#[ElementType('array')] class ArrayElementTestArr extends ArrayType
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
}
