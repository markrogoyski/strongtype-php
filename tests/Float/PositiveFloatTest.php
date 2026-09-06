<?php

declare(strict_types=1);

namespace StrongType\Tests\Float;

use StrongType\Exception\StrongTypeException;
use StrongType\Float\PositiveFloat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PositiveFloatTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(float $value)
    {
        // When
        $nonnegativeFloat = new PositiveFloat($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(float $value)
    {
        // Given
        $nonnegativeFloat = new PositiveFloat($value);

        // When
        $obtainedValue = $nonnegativeFloat->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(float $value)
    {
        // Given
        $nonnegativeFloat = new PositiveFloat($value);

        // When
        $debugInfo = $nonnegativeFloat->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [1.0],
            [2.2],
            [3.945],
            [10.1],
            [1000.005],
            [8973298792.777],
            [\PHP_FLOAT_MIN],
            [\PHP_FLOAT_MAX],
            [1.2345678901234567],
            [1.0e+20],
            [1.0e-20],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(float $value, string $expected)
    {
        // Given
        $nonnegativeFloat = new PositiveFloat($value);

        // When
        $stringRepresentation = (string) $nonnegativeFloat;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(float $value, string $expected)
    {
        // Given
        $nonnegativeFloat = new PositiveFloat($value);

        // When
        $jsonSerialization = \json_encode($nonnegativeFloat);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [1.0, '1'],
            [2.2, '2.2'],
            [3.945, '3.945'],
            [10.1, '10.1'],
            [1000.005, '1000.005'],
            [8973298792.777, '8973298792.777'],
            // High-precision and scientific-notation values, where __toString's
            // json_encode rendering differs from strval's 14-digit, upper-case-E one.
            [1.2345678901234567, '1.2345678901234567'],
            [1.0e+20, '1.0e+20'],
            [1.0e-20, '1.0e-20'],
            [\PHP_FLOAT_MAX, '1.7976931348623157e+308'],
            [\PHP_FLOAT_MIN, '2.2250738585072014e-308'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testStringRepresentationMatchesJsonEncoding(float $value)
    {
        // Given
        $positiveFloat = new PositiveFloat($value);

        // When
        $stringRepresentation = (string) $positiveFloat;

        // Then
        $this->assertSame(\json_encode($positiveFloat), $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(float $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $nonnegativeFloat = new PositiveFloat($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [0.0],
            [-1.0],
            [-2.2],
            [-10.1],
            [-1000.005],
            [-8973298792.777],
            [-\PHP_FLOAT_MIN],
            [-\PHP_FLOAT_MAX],
        ];
    }
}
