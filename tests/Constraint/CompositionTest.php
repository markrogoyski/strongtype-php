<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\ElementType;
use StrongType\Constraint\Max;
use StrongType\Constraint\MaxLength;
use StrongType\Constraint\Min;
use StrongType\Constraint\Nonempty;
use StrongType\Constraint\Pattern;
use StrongType\Constraint\Unique;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;
use StrongType\String\StringType;
use StrongType\Arrays\ArrayType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Min(1), Max(100)]
readonly class TestPercentage extends Integer {}

#[Nonempty, Pattern('/^[a-z]+$/'), MaxLength(10)]
readonly class TestShortSlug extends StringType {}

#[Nonempty, Unique, ElementType('string')]
class TestUniqueStrings extends ArrayType {}

class CompositionTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidPercentages')]
    public function testValidPercentage(int $value)
    {
        $pct = new TestPercentage($value);
        $this->assertSame($value, $pct->value);
    }

    public static function dataProviderForValidPercentages(): array
    {
        return [[1], [50], [100]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidPercentages')]
    public function testInvalidPercentage(int $value)
    {
        $this->expectException(StrongTypeException::class);
        new TestPercentage($value);
    }

    public static function dataProviderForInvalidPercentages(): array
    {
        return [[0], [-1], [101], [1000]];
    }

    #[Test]
    #[DataProvider('dataProviderForValidSlugs')]
    public function testValidShortSlug(string $value)
    {
        $slug = new TestShortSlug($value);
        $this->assertSame($value, $slug->value);
    }

    public static function dataProviderForValidSlugs(): array
    {
        return [['a'], ['hello'], ['abcdefghij']];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidSlugs')]
    public function testInvalidShortSlug(string $value)
    {
        $this->expectException(StrongTypeException::class);
        new TestShortSlug($value);
    }

    public static function dataProviderForInvalidSlugs(): array
    {
        return [
            [''],              // Nonempty
            ['Hello'],         // Pattern (uppercase)
            ['abcdefghijk'],   // MaxLength (11 chars)
            ['with spaces'],   // Pattern
        ];
    }

    #[Test]
    public function testValidUniqueStrings()
    {
        $arr = new TestUniqueStrings(['a', 'b', 'c']);
        $this->assertSame(['a', 'b', 'c'], $arr->values);
    }

    #[Test]
    public function testUniqueStringsRejectsEmpty()
    {
        $this->expectException(StrongTypeException::class);
        new TestUniqueStrings([]);
    }

    #[Test]
    public function testUniqueStringsRejectsDuplicates()
    {
        $this->expectException(StrongTypeException::class);
        new TestUniqueStrings(['a', 'a']);
    }

    #[Test]
    public function testUniqueStringsRejectsWrongType()
    {
        $this->expectException(StrongTypeException::class);
        new TestUniqueStrings([1, 2, 3]);
    }
}
