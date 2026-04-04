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
        ];
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
