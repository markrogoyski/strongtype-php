<?php

declare(strict_types=1);

namespace StrongType\Tests\Constraint;

use StrongType\Constraint\Max;
use StrongType\Exception\ConstraintConfigurationException;
use StrongType\Exception\StrongTypeException;
use StrongType\Float\FloatingPoint;
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

#[Max(1.0)]
readonly class MaxTestFloat extends FloatingPoint
{
}

#[Max(1.5, exclusive: true)]
readonly class MaxExclusiveTestFloat extends FloatingPoint
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

    #[Test]
    public function testMaxFloatAcceptsTheBound()
    {
        // When
        $float = new MaxTestFloat(1.0);

        // Then
        $this->assertSame(1.0, $float->value);
    }

    #[Test]
    public function testMaxFloatRejectsAboveTheBound()
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        new MaxTestFloat(1.1);
    }

    /**
     * The bound is rendered with var_export, so a float bound keeps its float spelling
     * (1.0, not 1) and reads consistently with InRange's message.
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
            'int bound, inclusive'     => [MaxTestInt::class, 101, 'MaxTestInt type must be <= 100, got 101'],
            'int bound, exclusive'     => [MaxExclusiveTestInt::class, 100, 'MaxExclusiveTestInt type must be < 100, got 100'],
            'float bound, inclusive'   => [MaxTestFloat::class, 1.5, 'MaxTestFloat type must be <= 1.0, got 1.5'],
            'float bound, exclusive'   => [MaxExclusiveTestFloat::class, 1.5, 'MaxExclusiveTestFloat type must be < 1.5, got 1.5'],
            'float bound, int-ish arg' => [MaxTestFloat::class, 2.0, 'MaxTestFloat type must be <= 1.0, got 2'],
        ];
    }

    /**
     * NAN compares false against everything, so a NAN bound would never reject a value --
     * the same fail-open shape as a misapplied constraint, so it is rejected up front.
     */
    #[Test]
    public function testNanBoundIsRejected(): void
    {
        // Then
        $this->expectException(ConstraintConfigurationException::class);

        // When
        new Max(\NAN);
    }
}
