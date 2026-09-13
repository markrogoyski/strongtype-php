<?php

declare(strict_types=1);

namespace StrongType\Tests;

use StrongType\Util\ValueEquality;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ValueEqualityTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForSamePairs')]
    public function testSame(mixed $a, mixed $b, bool $expected)
    {
        // When
        $same = ValueEquality::same($a, $b);

        // Then
        $this->assertSame($expected, $same);
    }

    public static function dataProviderForSamePairs(): array
    {
        return [
            'identical ints'            => [1, 1, true],
            'different ints'            => [1, 2, false],
            'int vs numeric string'     => [1, '1', false],
            'bool vs int'               => [true, 1, false],
            'identical strings'         => ['abc', 'abc', true],
            'different strings'         => ['abc', 'abd', false],
            'identical floats'          => [1.5, 1.5, true],
            'different floats'          => [1.5, 2.5, false],
            'NAN vs NAN'                => [\NAN, \NAN, true],
            'NAN vs a real float'       => [\NAN, 1.0, false],
            'NAN vs an int'             => [\NAN, 1, false],
            'INF vs INF'                => [\INF, \INF, true],
            'INF vs -INF'               => [\INF, -\INF, false],
            'identical arrays'          => [[1, 2], [1, 2], true],
            'differently ordered arrays' => [[1, 2], [2, 1], false],
            'null vs null'              => [null, null, true],
            'null vs empty string'      => [null, '', false],
            'nested arrays, same shape'       => [[1, [2, 3]], [1, [2, 3]], true],
            'nested arrays, different leaf'   => [[1, [2, 3]], [1, [2, 4]], false],
            'nested array vs non-array leaf'  => [[1, [2, 3]], [1, 'x'], false],
            'different key sets'              => [['a' => 1], ['b' => 1], false],
            // The NAN allowance is a top-level rule: a NAN nested inside an array element
            // is compared strictly (native === semantics), same as strictSame().
            'nested NAN leaves are strict'    => [[1, \NAN], [1, \NAN], false],
        ];
    }

    /**
     * Native `===` throws ("Nesting level too deep") comparing two distinct
     * self-referential arrays directly -- same() must catch that rather than let it
     * escape, for a legal, explicitly supported ArrayType element (see __toString()).
     * There is no safe way to determine whether two arrays that both cycle back on
     * themselves are "really" equal without risking the same unbounded recursion, so this
     * reports false (never a false claim of equality) rather than attempt one.
     */
    #[Test]
    public function testSelfReferentialArraysDoNotThrowAndAreConservativelyNotSame(): void
    {
        // Given
        $a = [];
        $a['self'] = &$a;
        $b = [];
        $b['self'] = &$b;

        // When
        $same = ValueEquality::same($a, $b);

        // Then
        $this->assertFalse($same);
    }

    #[Test]
    public function testDeeplyNestedNonCyclicArraysCompareExactly(): void
    {
        // Given two identical, non-cyclic structures 50 levels deep. Native === handles
        // ordinary deep nesting directly -- it only throws on an actual cycle -- so this
        // stays fully precise.
        $a = 'leaf';
        for ($i = 0; $i < 50; $i++) {
            $a = [$a];
        }
        $b = $a;

        // When / Then
        $this->assertTrue(ValueEquality::same($a, $b));
    }

    #[Test]
    public function testDeeplyNestedNonCyclicArraysDetectADifference(): void
    {
        // Given two structures 50 levels deep that diverge only at the leaf.
        $a = 'leaf-a';
        $b = 'leaf-b';
        for ($i = 0; $i < 50; $i++) {
            $a = [$a];
            $b = [$b];
        }

        // When / Then
        $this->assertFalse(ValueEquality::same($a, $b));
    }

    #[Test]
    #[DataProvider('dataProviderForStrictSamePairs')]
    public function testStrictSame(mixed $a, mixed $b, bool $expected): void
    {
        // When
        $same = ValueEquality::strictSame($a, $b);

        // Then
        $this->assertSame($expected, $same);
    }

    public static function dataProviderForStrictSamePairs(): array
    {
        return [
            'identical ints'      => [1, 1, true],
            'different ints'      => [1, 2, false],
            // Unlike same(), strictSame() has no NAN allowance -- matches native ===.
            'NAN vs NAN'          => [\NAN, \NAN, false],
            'nested NAN leaves'   => [[1, \NAN], [1, \NAN], false],
            'identical arrays'    => [[1, 2], [1, 2], true],
            'different key sets'  => [['a' => 1], ['b' => 1], false],
        ];
    }

    #[Test]
    public function testStrictSameHandlesSelfReferentialArraysWithoutThrowing(): void
    {
        // Given
        $a = [];
        $a['self'] = &$a;
        $b = [];
        $b['self'] = &$b;

        // When
        $same = ValueEquality::strictSame($a, $b);

        // Then
        $this->assertFalse($same);
    }

    /**
     * Regression test for a real bug in an earlier version: the crash-safe fallback used
     * to walk a bounded number of levels and assume "beyond the bound, treat as equal."
     * That is unsound -- there is no way to distinguish "these are the same infinite
     * cycle" from "these are two long, genuinely different chains that happen to still
     * match key-for-key this far down, with a real difference one level further" without
     * risking the exact unbounded recursion that made native === throw in the first
     * place. Two long, distinct cycles -- whether their content is identical or, as here,
     * diverges at a leaf -- must both report false, and must do so without crashing
     * regardless of how many links the cycle takes to close.
     */
    #[Test]
    public function testLongDivergingCyclesDoNotThrowAndAreNotFalselyReportedEqual(): void
    {
        // Given
        $a = self::buildCycleWithLeafAt(100, 5, 'a');
        $b = self::buildCycleWithLeafAt(100, 5, 'b');

        // When / Then
        $this->assertFalse(ValueEquality::same($a, $b));
    }

    #[Test]
    public function testLongMatchingCyclesDoNotThrowAndAreConservativelyNotSame(): void
    {
        // Given two structurally identical cycles, 100 links before closing.
        $a = self::buildCycleWithLeafAt(100, 5, 'same');
        $b = self::buildCycleWithLeafAt(100, 5, 'same');

        // When / Then
        $this->assertFalse(ValueEquality::same($a, $b));
    }

    #[Test]
    public function testSameOrThrowLetsTheCyclicArrayErrorPropagate(): void
    {
        // Given
        $a = [];
        $a['self'] = &$a;
        $b = [];
        $b['self'] = &$b;

        // Then
        $this->expectException(\Error::class);

        // When
        ValueEquality::sameOrThrow($a, $b);
    }

    #[Test]
    public function testStrictSameOrThrowLetsTheCyclicArrayErrorPropagate(): void
    {
        // Given
        $a = [];
        $a['self'] = &$a;
        $b = [];
        $b['self'] = &$b;

        // Then
        $this->expectException(\Error::class);

        // When
        ValueEquality::strictSameOrThrow($a, $b);
    }

    #[Test]
    public function testSameOrThrowStillAllowsNanEquality(): void
    {
        // When / Then
        $this->assertTrue(ValueEquality::sameOrThrow(\NAN, \NAN));
    }

    /**
     * Builds a cycle of $length links, closing back on itself, with a 'leaf' key holding
     * $leafValue inserted at node $leafDepth (the 'next' key -- the cycle chain -- is
     * always set first at every node, so it always sorts ahead of 'leaf' in iteration
     * order, matching how a real self-referential structure's own links would appear).
     *
     * @return array<array-key, mixed>
     */
    private static function buildCycleWithLeafAt(int $length, int $leafDepth, string $leafValue): array
    {
        $head = [];
        $node = &$head;
        for ($i = 0; $i < $length; $i++) {
            $node['next'] = [];
            if ($i === $leafDepth) {
                $node['leaf'] = $leafValue;
            }
            $node = &$node['next'];
        }
        $node['next'] = &$head;
        return $head;
    }
}
