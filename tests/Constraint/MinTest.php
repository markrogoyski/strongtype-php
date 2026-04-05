<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Min;
use StrongType\Exception\StrongTypeException;
use StrongType\Int\Integer;
use StrongType\Float\FloatingPoint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[Min(0)]
readonly class MinTestInt extends Integer
{
}

#[Min(5, exclusive: true)]
readonly class MinExclusiveTestInt extends Integer
{
}

#[Min(0.0)]
readonly class MinTestFloat extends FloatingPoint
{
}

class MinTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidMinInt')]
    public function testValidMinInt(int $value)
    {
        $int = new MinTestInt($value);
        $this->assertSame($value, $int->value);
    }

    public static function dataProviderForValidMinInt(): array
    {
        return [[0], [1], [100], [\PHP_INT_MAX]];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidMinInt')]
    public function testInvalidMinInt(int $value)
    {
        $this->expectException(StrongTypeException::class);
        new MinTestInt($value);
    }

    public static function dataProviderForInvalidMinInt(): array
    {
        return [[-1], [-100], [\PHP_INT_MIN]];
    }

    #[Test]
    public function testExclusiveMinAcceptsSix()
    {
        $int = new MinExclusiveTestInt(6);
        $this->assertSame(6, $int->value);
    }

    #[Test]
    public function testExclusiveMinRejectsFive()
    {
        $this->expectException(StrongTypeException::class);
        new MinExclusiveTestInt(5);
    }

    #[Test]
    public function testMinFloat()
    {
        $float = new MinTestFloat(0.0);
        $this->assertSame(0.0, $float->value);
    }

    #[Test]
    public function testMinFloatRejectsNegative()
    {
        $this->expectException(StrongTypeException::class);
        new MinTestFloat(-0.1);
    }

    #[Test]
    public function testErrorMessageFormat()
    {
        try {
            new MinTestInt(-5);
            $this->fail('Expected exception');
        } catch (StrongTypeException $e) {
            $this->assertSame('MinTestInt type must be >= 0, got -5', $e->getMessage());
        }
    }
}
