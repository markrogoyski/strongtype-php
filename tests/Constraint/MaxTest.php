<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Max;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Max(100)]
readonly class MaxTestInt extends Integer
{
}

#[Max(100, exclusive: true)]
readonly class MaxExclusiveTestInt extends Integer
{
}

class MaxTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValid')]
    public function testValid(int $value)
    {
        $int = new MaxTestInt($value);
        $this->assertSame($value, $int->value);
    }

    public static function dataProviderForValid(): array
    {
        return [[100], [0], [-100], [\PHP_INT_MIN]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalid')]
    public function testInvalid(int $value)
    {
        $this->expectException(StrongTypeException::class);
        new MaxTestInt($value);
    }

    public static function dataProviderForInvalid(): array
    {
        return [[101], [1000], [\PHP_INT_MAX]];
    }

    #[Test]
    public function testExclusiveMaxAccepts99()
    {
        $int = new MaxExclusiveTestInt(99);
        $this->assertSame(99, $int->value);
    }

    #[Test]
    public function testExclusiveMaxRejects100()
    {
        $this->expectException(StrongTypeException::class);
        new MaxExclusiveTestInt(100);
    }

    #[Test]
    public function testErrorMessageFormat()
    {
        try {
            new MaxTestInt(200);
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            $this->assertSame('MaxTestInt type must be <= 100, got 200', $e->getMessage());
        }
    }
}
