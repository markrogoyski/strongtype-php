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

    #[Test]
    public function testReflexiveForArrayContainingNan(): void
    {
        // Given
        $a = new ListArray([1, \NAN, 3]);

        // When
        $result = $a->equalsUnordered($a);

        // Then equalsUnordered agrees with equals, which is reflexive here
        $this->assertTrue($result);
        $this->assertTrue($a->equals($a));
    }

    #[Test]
    public function testNanMatchesNanAcrossDistinctInstances(): void
    {
        // Given
        $a = new ListArray([\NAN, 1]);
        $b = new ListArray([1, \NAN]);

        // When
        $result = $a->equalsUnordered($b);

        // Then NAN is treated as one value, matching Unique's duplicate detection
        $this->assertTrue($result);
    }

    #[Test]
    public function testNanCountsAsAMultisetMember(): void
    {
        // Given the same NAN count on both sides, in different positions
        $a = new ListArray([\NAN, \NAN, 7]);
        $b = new ListArray([7, \NAN, \NAN]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertTrue($result);
    }

    #[Test]
    public function testDifferingNanCountsAreNotEqual(): void
    {
        // Given a differing number of NANs
        $a = new ListArray([\NAN, \NAN, 7]);
        $b = new ListArray([\NAN, 7, 7]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertFalse($result);
    }

    #[Test]
    public function testNanDoesNotMatchANonFloat(): void
    {
        // Given
        $a = new ListArray([\NAN]);
        $b = new ListArray(['NAN']);

        // When
        $result = $a->equalsUnordered($b);

        // Then the NAN allowance is limited to two float NANs
        $this->assertFalse($result);
        $this->assertFalse((new ListArray([\NAN]))->equalsUnordered(new ListArray([0.0])));
    }

    #[Test]
    public function testReflexiveForNestedArrayContainingNan(): void
    {
        // Given
        $a = new ListArray([[\NAN], ['x' => \NAN]]);

        // When
        $result = $a->equalsUnordered($a);

        // Then
        $this->assertTrue($result);
    }

    /**
     * The NAN allowance is a top-level element rule. A NAN inside a nested element array is
     * compared by PHP's array comparison, which is strict, so two independently built nested
     * arrays do not match.
     */
    #[Test]
    public function testNanNestedInsideAnElementIsStillComparedStrictly(): void
    {
        // Given two independently constructed arrays with an identical nested shape
        $a = new ListArray([[\NAN]]);
        $b = new ListArray([[\NAN]]);

        // When
        $result = $a->equalsUnordered($b);

        // Then
        $this->assertFalse($result);
    }

    #[Test]
    public function testNanMatchIsSymmetric(): void
    {
        // Given
        $a = new ListArray([\NAN, 1]);
        $b = new ListArray([1, \NAN]);

        // When
        $forward = $a->equalsUnordered($b);
        $reverse = $b->equalsUnordered($a);

        // Then
        $this->assertTrue($forward);
        $this->assertSame($forward, $reverse);
    }

    #[Test]
    public function testDifferingNanCountsAreNotEqualInEitherDirection(): void
    {
        // Given
        $a = new ListArray([\NAN, \NAN, 7]);
        $b = new ListArray([\NAN, 7, 7]);

        // When
        $forward = $a->equalsUnordered($b);
        $reverse = $b->equalsUnordered($a);

        // Then
        $this->assertFalse($forward);
        $this->assertFalse($reverse);
    }

    /**
     * The two methods already differ by design on keys and order. NAN is the only place they
     * differ in how they compare an element, so it is the only case where they disagree with
     * keys and order matching. Locked in so the divergence stays deliberate and stays
     * documented (docs/semantics.md#equality-semantics).
     */
    #[Test]
    public function testNanIsTheOnlyElementComparisonDivergenceFromEquals(): void
    {
        // Given
        $a = new ListArray([\NAN]);
        $b = new ListArray([\NAN]);

        // When
        $unordered = $a->equalsUnordered($b);
        $ordered = $a->equals($b);

        // Then
        $this->assertTrue($unordered);
        $this->assertFalse($ordered);
        // ...while equals() is still reflexive on one instance, via PHP's array-identity shortcut
        $this->assertTrue($a->equals($a));
    }
}
