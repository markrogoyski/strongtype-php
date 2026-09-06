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

#[Min(1.5, exclusive: true)]
readonly class MinExclusiveTestFloat extends FloatingPoint
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

    /**
     * The bound is rendered with var_export, so a float bound keeps its float spelling
     * (0.0, not 0) and reads consistently with InRange's message.
     */
    #[Test]
    #[DataProvider('dataProviderForBoundRendering')]
    public function testBoundIsRenderedWithItsOwnType(string $class, int|float $value, string $expectedMessage)
    {
        // Then
        $this->expectException(StrongTypeException::class);
        $this->expectExceptionMessage($expectedMessage);

        // When
        new $class($value);
    }

    public static function dataProviderForBoundRendering(): array
    {
        return [
            'int bound, inclusive'     => [MinTestInt::class, -1, 'MinTestInt type must be >= 0, got -1'],
            'int bound, exclusive'     => [MinExclusiveTestInt::class, 5, 'MinExclusiveTestInt type must be > 5, got 5'],
            'float bound, inclusive'   => [MinTestFloat::class, -0.5, 'MinTestFloat type must be >= 0.0, got -0.5'],
            'float bound, exclusive'   => [MinExclusiveTestFloat::class, 1.5, 'MinExclusiveTestFloat type must be > 1.5, got 1.5'],
            'float bound, int-ish arg' => [MinTestFloat::class, -1.0, 'MinTestFloat type must be >= 0.0, got -1'],
        ];
    }
}
