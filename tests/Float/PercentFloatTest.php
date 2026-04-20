<?php

declare(strict_types=1);

namespace StrongType\Tests\Float;

use StrongType\Exception\StrongTypeException;
use StrongType\Float\PercentFloat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PercentFloatTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(float $value)
    {
        // When
        $percentFloat = new PercentFloat($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(float $value)
    {
        // Given
        $percentFloat = new PercentFloat($value);

        // When
        $obtainedValue = $percentFloat->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(float $value)
    {
        // Given
        $percentFloat = new PercentFloat($value);

        // When
        $debugInfo = $percentFloat->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [0.0],
            [50.0],
            [100.0],
            [99.9],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(float $value, string $expected)
    {
        // Given
        $percentFloat = new PercentFloat($value);

        // When
        $stringRepresentation = (string) $percentFloat;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(float $value, string $expected)
    {
        // Given
        $percentFloat = new PercentFloat($value);

        // When
        $jsonSerialization = \json_encode($percentFloat);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0.0, '0'],
            [50.0, '50'],
            [100.0, '100'],
            [99.9, '99.9'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(float $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $percentFloat = new PercentFloat($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [-0.1],
            [100.1],
            [-1.0],
            [200.0],
        ];
    }
}
