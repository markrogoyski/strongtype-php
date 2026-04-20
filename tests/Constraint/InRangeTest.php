<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\InRange;
use StrongType\Exception\StrongTypeException;
use StrongType\Float\FloatingPoint;
use StrongType\Int\Integer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[InRange(1, 100)]
readonly class InRangeInclusiveTestInt extends Integer
{
}

#[InRange(1, 100, exclusiveMin: true)]
readonly class InRangeExclusiveMinTestInt extends Integer
{
}

#[InRange(1, 100, exclusiveMax: true)]
readonly class InRangeExclusiveMaxTestInt extends Integer
{
}

#[InRange(1, 100, exclusiveMin: true, exclusiveMax: true)]
readonly class InRangeBothExclusiveTestInt extends Integer
{
}

#[InRange(0.0, 1.0)]
readonly class InRangeTestFloat extends FloatingPoint
{
}

class InRangeTest extends \PHPUnit\Framework\TestCase
{
    // Inclusive range tests

    #[Test]
    #[DataProvider('dataProviderForValidInclusive')]
    public function testValidInclusive(int $value): void
    {
        $int = new InRangeInclusiveTestInt($value);
        $this->assertSame($value, $int->value);
    }

    public static function dataProviderForValidInclusive(): array
    {
        return [[1], [50], [100]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidInclusive')]
    public function testInvalidInclusive(int $value): void
    {
        $this->expectException(StrongTypeException::class);
        new InRangeInclusiveTestInt($value);
    }

    public static function dataProviderForInvalidInclusive(): array
    {
        return [[0], [-1], [101], [1000]];
    }

    // Exclusive min tests

    #[Test]
    #[DataProvider('dataProviderForValidExclusiveMin')]
    public function testValidExclusiveMin(int $value): void
    {
        $int = new InRangeExclusiveMinTestInt($value);
        $this->assertSame($value, $int->value);
    }

    public static function dataProviderForValidExclusiveMin(): array
    {
        return [[2], [50], [100]];
    }

    #[Test]
    public function testExclusiveMinRejectsMinBoundary(): void
    {
        $this->expectException(StrongTypeException::class);
        new InRangeExclusiveMinTestInt(1);
    }

    // Exclusive max tests

    #[Test]
    #[DataProvider('dataProviderForValidExclusiveMax')]
    public function testValidExclusiveMax(int $value): void
    {
        $int = new InRangeExclusiveMaxTestInt($value);
        $this->assertSame($value, $int->value);
    }

    public static function dataProviderForValidExclusiveMax(): array
    {
        return [[1], [50], [99]];
    }

    #[Test]
    public function testExclusiveMaxRejectsMaxBoundary(): void
    {
        $this->expectException(StrongTypeException::class);
        new InRangeExclusiveMaxTestInt(100);
    }

    // Both exclusive tests

    #[Test]
    #[DataProvider('dataProviderForValidBothExclusive')]
    public function testValidBothExclusive(int $value): void
    {
        $int = new InRangeBothExclusiveTestInt($value);
        $this->assertSame($value, $int->value);
    }

    public static function dataProviderForValidBothExclusive(): array
    {
        return [[2], [50], [99]];
    }

    #[Test]
    public function testBothExclusiveRejectsMinBoundary(): void
    {
        $this->expectException(StrongTypeException::class);
        new InRangeBothExclusiveTestInt(1);
    }

    #[Test]
    public function testBothExclusiveRejectsMaxBoundary(): void
    {
        $this->expectException(StrongTypeException::class);
        new InRangeBothExclusiveTestInt(100);
    }

    // Float tests

    #[Test]
    #[DataProvider('dataProviderForValidFloat')]
    public function testValidFloat(float $value): void
    {
        $float = new InRangeTestFloat($value);
        $this->assertSame($value, $float->value);
    }

    public static function dataProviderForValidFloat(): array
    {
        return [[0.0], [0.5], [1.0]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidFloat')]
    public function testInvalidFloat(float $value): void
    {
        $this->expectException(StrongTypeException::class);
        new InRangeTestFloat($value);
    }

    public static function dataProviderForInvalidFloat(): array
    {
        return [[-0.1], [1.1], [100.0]];
    }

    // LogicException tests

    #[Test]
    public function testLogicExceptionWhenMinGreaterThanMax(): void
    {
        $this->expectException(\LogicException::class);
        new InRange(100, 1);
    }

    #[Test]
    public function testLogicExceptionWhenEqualWithExclusiveMin(): void
    {
        $this->expectException(\LogicException::class);
        new InRange(5, 5, exclusiveMin: true);
    }

    #[Test]
    public function testLogicExceptionWhenEqualWithExclusiveMax(): void
    {
        $this->expectException(\LogicException::class);
        new InRange(5, 5, exclusiveMax: true);
    }

    #[Test]
    public function testEqualMinMaxInclusiveIsValid(): void
    {
        $range = new InRange(5, 5);
        $this->assertNull($range->validate(5, 'Test'));
    }

    // Error message format tests

    #[Test]
    public function testErrorMessageInclusiveFormat(): void
    {
        try {
            new InRangeInclusiveTestInt(0);
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            $this->assertSame('InRangeInclusiveTestInt type must be in range [1, 100], got 0', $e->getMessage());
        }
    }

    #[Test]
    public function testErrorMessageExclusiveFormat(): void
    {
        try {
            new InRangeBothExclusiveTestInt(1);
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            $this->assertSame('InRangeBothExclusiveTestInt type must be in range (1, 100), got 1', $e->getMessage());
        }
    }

    #[Test]
    public function testErrorMessageFloatBoundsRenderAsFloat(): void
    {
        try {
            new InRangeTestFloat(-0.5);
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            $this->assertSame('InRangeTestFloat type must be in range [0.0, 1.0], got -0.5', $e->getMessage());
        }
    }
}
