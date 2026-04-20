<?php

declare(strict_types=1);

namespace StrongType\Tests\Float;

use StrongType\Exception\StrongTypeException;
use StrongType\Float\Latitude;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class LatitudeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(float $value)
    {
        // When
        $latitude = new Latitude($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(float $value)
    {
        // Given
        $latitude = new Latitude($value);

        // When
        $obtainedValue = $latitude->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(float $value)
    {
        // Given
        $latitude = new Latitude($value);

        // When
        $debugInfo = $latitude->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [0.0],
            [90.0],
            [-90.0],
            [45.5],
            [-45.5],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(float $value, string $expected)
    {
        // Given
        $latitude = new Latitude($value);

        // When
        $stringRepresentation = (string) $latitude;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(float $value, string $expected)
    {
        // Given
        $latitude = new Latitude($value);

        // When
        $jsonSerialization = \json_encode($latitude);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0.0, '0'],
            [90.0, '90'],
            [-90.0, '-90'],
            [45.5, '45.5'],
            [-45.5, '-45.5'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(float $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $latitude = new Latitude($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [90.1],
            [-90.1],
            [180.0],
            [-180.0],
        ];
    }
}
