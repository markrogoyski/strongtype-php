<?php

declare(strict_types=1);

namespace StrongType\Tests\Float;

use StrongType\Exception\StrongTypeException;
use StrongType\Float\UnitFloat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class UnitFloatTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(float $value)
    {
        // When
        $unitFloat = new UnitFloat($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(float $value)
    {
        // Given
        $unitFloat = new UnitFloat($value);

        // When
        $obtainedValue = $unitFloat->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(float $value)
    {
        // Given
        $unitFloat = new UnitFloat($value);

        // When
        $debugInfo = $unitFloat->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [0.0],
            [0.1],
            [0.25],
            [0.5],
            [0.75],
            [0.999],
            [1.0],
            [0.1234567890123456],
            [1 / 3],
            [1.0e-20],
            [\PHP_FLOAT_EPSILON],
            [\PHP_FLOAT_MIN],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(float $value, string $expected)
    {
        // Given
        $unitFloat = new UnitFloat($value);

        // When
        $stringRepresentation = (string) $unitFloat;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(float $value, string $expected)
    {
        // Given
        $unitFloat = new UnitFloat($value);

        // When
        $jsonSerialization = \json_encode($unitFloat);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0.0, '0'],
            [0.1, '0.1'],
            [0.25, '0.25'],
            [0.5, '0.5'],
            [0.75, '0.75'],
            [0.999, '0.999'],
            [1.0, '1'],
            // High-precision and scientific-notation values: __toString renders these
            // through json_encode, so both forms agree digit for digit (the shortest
            // representation that round-trips, lower-case exponent) instead of diverging
            // on strval's 14-digit `precision` ini and upper-case `E`.
            [0.1234567890123456, '0.1234567890123456'],
            [1 / 3, '0.3333333333333333'],
            [1.0e-20, '1.0e-20'],
            [\PHP_FLOAT_EPSILON, '2.220446049250313e-16'],
            [\PHP_FLOAT_MIN, '2.2250738585072014e-308'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testStringRepresentationMatchesJsonEncoding(float $value)
    {
        // Given
        $unitFloat = new UnitFloat($value);

        // When
        $stringRepresentation = (string) $unitFloat;

        // Then
        $this->assertSame(\json_encode($unitFloat), $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(float $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $unitFloat = new UnitFloat($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [-0.1],
            [-1.0],
            [1.1],
            [2.0],
            [100.0],
            [-\PHP_FLOAT_MAX],
            [\PHP_FLOAT_MAX],
        ];
    }
}
