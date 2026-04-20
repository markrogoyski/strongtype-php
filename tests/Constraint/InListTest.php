<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\InList;
use StrongType\Exception\StrongTypeException;
use StrongType\Float\FloatingPoint;
use StrongType\Int\Integer;
use StrongType\String\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[InList(1, 2, 3)]
readonly class InListTestInt extends Integer
{
}

#[InList('red', 'green', 'blue')]
readonly class InListTestString extends StringType
{
}

#[InList(1.5, 2.5, 3.5)]
readonly class InListTestFloat extends FloatingPoint
{
}

class InListTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidInts')]
    public function testValidInts(int $value): void
    {
        $int = new InListTestInt($value);
        $this->assertSame($value, $int->value);
    }

    public static function dataProviderForValidInts(): array
    {
        return [[1], [2], [3]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidInts')]
    public function testInvalidInts(int $value): void
    {
        $this->expectException(StrongTypeException::class);
        new InListTestInt($value);
    }

    public static function dataProviderForInvalidInts(): array
    {
        return [[0], [4], [-1], [100]];
    }

    #[Test]
    #[DataProvider('dataProviderForValidStrings')]
    public function testValidStrings(string $value): void
    {
        $str = new InListTestString($value);
        $this->assertSame($value, $str->value);
    }

    public static function dataProviderForValidStrings(): array
    {
        return [['red'], ['green'], ['blue']];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidStrings')]
    public function testInvalidStrings(string $value): void
    {
        $this->expectException(StrongTypeException::class);
        new InListTestString($value);
    }

    public static function dataProviderForInvalidStrings(): array
    {
        return [['yellow'], ['RED'], ['']];
    }

    #[Test]
    #[DataProvider('dataProviderForValidFloats')]
    public function testValidFloats(float $value): void
    {
        $float = new InListTestFloat($value);
        $this->assertSame($value, $float->value);
    }

    public static function dataProviderForValidFloats(): array
    {
        return [[1.5], [2.5], [3.5]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidFloats')]
    public function testInvalidFloats(float $value): void
    {
        $this->expectException(StrongTypeException::class);
        new InListTestFloat($value);
    }

    public static function dataProviderForInvalidFloats(): array
    {
        return [[0.0], [1.0], [4.5]];
    }

    #[Test]
    public function testErrorMessageFormat(): void
    {
        try {
            new InListTestInt(99);
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            $this->assertSame('InListTestInt type must be one of [1, 2, 3], got 99', $e->getMessage());
        }
    }

    #[Test]
    public function testLogicExceptionWhenNoAllowedValues(): void
    {
        $this->expectException(\LogicException::class);
        new InList();
    }
}
