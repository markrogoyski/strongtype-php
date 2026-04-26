<?php

declare(strict_types=1);

namespace StrongType\Tests\Arrays;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StrongType\Arrays\ListArray;
use StrongType\Arrays\NonemptyArray;
use StrongType\Arrays\UniqueArray;
use StrongType\Int\PositiveInt;

class EqualsUnorderedTest extends TestCase
{
    #[Test]
    public function testEqualSameValuesSameOrder(): void
    {
        // Given
        $a = new NonemptyArray([1, 2, 3]);
        $b = new NonemptyArray([1, 2, 3]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertTrue($result);
    }

    #[Test]
    public function testEqualSameValuesDifferentOrder(): void
    {
        // Given
        $a = new NonemptyArray([1, 2, 3]);
        $b = new NonemptyArray([3, 1, 2]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertTrue($result);
    }

    #[Test]
    public function testEqualSameValuesDifferentKeys(): void
    {
        // Given
        $a = new NonemptyArray(['x' => 1, 'y' => 2]);
        $b = new NonemptyArray(['a' => 2, 'b' => 1]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertTrue($result);
    }

    #[Test]
    public function testEqualMultisetCountsDuplicates(): void
    {
        // Given values with repeated entries
        $a = new ListArray([1, 2, 2, 3]);
        $b = new ListArray([3, 2, 1, 2]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertTrue($result);
    }

    #[Test]
    public function testNotEqualWhenMultisetCountsDiffer(): void
    {
        // Given different multiplicities of the same set of values
        $a = new ListArray([1, 2, 2]);
        $b = new ListArray([1, 1, 2]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertFalse($result);
    }

    #[Test]
    public function testNotEqualDifferentLengths(): void
    {
        // Given
        $a = new NonemptyArray([1, 2, 3]);
        $b = new NonemptyArray([1, 2, 3, 4]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertFalse($result);
    }

    #[Test]
    public function testNotEqualDifferentValues(): void
    {
        // Given
        $a = new NonemptyArray([1, 2, 3]);
        $b = new NonemptyArray([4, 5, 6]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertFalse($result);
    }

    #[Test]
    public function testNotEqualAcrossSubclassesSameValues(): void
    {
        // Given two distinct ArrayType subclasses with identical contents
        $a = new ListArray([1, 2, 3]);
        $b = new UniqueArray([3, 2, 1]);

        // When
        $result = $a->equalsUnordered($b);

        // Then equalsUnordered is type-strict, like equals
        $this->assertFalse($result);
        $this->assertFalse($b->equalsUnordered($a));
    }

    #[Test]
    public function testNotEqualAgainstNonArrayHasEquals(): void
    {
        // Given an ArrayType and a scalar HasEquals
        $a = new ListArray([1, 2, 3]);
        $b = new PositiveInt(1);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertFalse($result);
    }

    #[Test]
    public function testStrictTypeComparison(): void
    {
        // Given values that are == but not === (string vs int)
        $a = new ListArray([1, 2, 3]);
        $b = new ListArray(['1', '2', '3']);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertFalse($result);
    }

    #[Test]
    public function testEmptyArraysEqual(): void
    {
        // Given
        $a = new ListArray([]);
        $b = new ListArray([]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertTrue($result);
    }
}
