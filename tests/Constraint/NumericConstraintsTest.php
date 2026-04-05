<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Positive;
use StrongType\Constraint\Negative;
use StrongType\Constraint\Nonnegative;
use StrongType\Constraint\Nonpositive;
use StrongType\Constraint\Nonzero;
use StrongType\Constraint\Even;
use StrongType\Constraint\Odd;
use StrongType\Constraint\DivisibleBy;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;
use StrongType\Float\FloatingPoint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Positive] readonly class PositiveTestInt extends Integer
{
}
#[Negative] readonly class NegativeTestInt extends Integer
{
}
#[Nonnegative] readonly class NonnegativeTestInt extends Integer
{
}
#[Nonpositive] readonly class NonpositiveTestInt extends Integer
{
}
#[Nonzero] readonly class NonzeroTestInt extends Integer
{
}
#[Even] readonly class EvenTestInt extends Integer
{
}
#[Odd] readonly class OddTestInt extends Integer
{
}
#[DivisibleBy(3)] readonly class DivisibleBy3TestInt extends Integer
{
}
#[Positive] readonly class PositiveTestFloat extends FloatingPoint
{
}

class NumericConstraintsTest extends \PHPUnit\Framework\TestCase
{
    // Positive
    #[Test]
    public function testPositiveAccepts()
    {
        $this->assertSame(1, (new PositiveTestInt(1))->value);
        $this->assertSame(0.5, (new PositiveTestFloat(0.5))->value);
    }

    #[Test]
    public function testPositiveRejectsZero()
    {
        $this->expectException(StrongTypeException::class);
        new PositiveTestInt(0);
    }

    #[Test]
    public function testPositiveRejectsNegative()
    {
        $this->expectException(StrongTypeException::class);
        new PositiveTestInt(-1);
    }

    // Negative
    #[Test]
    public function testNegativeAccepts()
    {
        $this->assertSame(-1, (new NegativeTestInt(-1))->value);
    }

    #[Test]
    public function testNegativeRejectsZero()
    {
        $this->expectException(StrongTypeException::class);
        new NegativeTestInt(0);
    }

    #[Test]
    public function testNegativeRejectsPositive()
    {
        $this->expectException(StrongTypeException::class);
        new NegativeTestInt(1);
    }

    // Nonnegative
    #[Test]
    public function testNonnegativeAcceptsZero()
    {
        $this->assertSame(0, (new NonnegativeTestInt(0))->value);
    }

    #[Test]
    public function testNonnegativeRejectsNegative()
    {
        $this->expectException(StrongTypeException::class);
        new NonnegativeTestInt(-1);
    }

    // Nonpositive
    #[Test]
    public function testNonpositiveAcceptsZero()
    {
        $this->assertSame(0, (new NonpositiveTestInt(0))->value);
    }

    #[Test]
    public function testNonpositiveRejectsPositive()
    {
        $this->expectException(StrongTypeException::class);
        new NonpositiveTestInt(1);
    }

    // Nonzero
    #[Test]
    public function testNonzeroAccepts()
    {
        $this->assertSame(1, (new NonzeroTestInt(1))->value);
        $this->assertSame(-1, (new NonzeroTestInt(-1))->value);
    }

    #[Test]
    public function testNonzeroRejectsZero()
    {
        $this->expectException(StrongTypeException::class);
        new NonzeroTestInt(0);
    }

    // Even
    #[Test]
    #[DataProvider('dataProviderForValidEven')]
    public function testEvenAccepts(int $value)
    {
        $this->assertSame($value, (new EvenTestInt($value))->value);
    }

    public static function dataProviderForValidEven(): array
    {
        return [[0], [2], [-2], [100], [-100]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidEven')]
    public function testEvenRejects(int $value)
    {
        $this->expectException(StrongTypeException::class);
        new EvenTestInt($value);
    }

    public static function dataProviderForInvalidEven(): array
    {
        return [[1], [-1], [3], [99]];
    }

    // Odd
    #[Test]
    #[DataProvider('dataProviderForValidOdd')]
    public function testOddAccepts(int $value)
    {
        $this->assertSame($value, (new OddTestInt($value))->value);
    }

    public static function dataProviderForValidOdd(): array
    {
        return [[1], [-1], [3], [99]];
    }

    #[Test]
    public function testOddRejectsEven()
    {
        $this->expectException(StrongTypeException::class);
        new OddTestInt(2);
    }

    // DivisibleBy
    #[Test]
    #[DataProvider('dataProviderForDivisibleBy3')]
    public function testDivisibleBy3Accepts(int $value)
    {
        $this->assertSame($value, (new DivisibleBy3TestInt($value))->value);
    }

    public static function dataProviderForDivisibleBy3(): array
    {
        return [[0], [3], [6], [-3], [99]];
    }

    #[Test]
    public function testDivisibleBy3RejectsNonDivisible()
    {
        $this->expectException(StrongTypeException::class);
        new DivisibleBy3TestInt(1);
    }
}
