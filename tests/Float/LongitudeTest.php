<?php

declare(strict_types=1);

namespace StrongType\Tests\Float;

use StrongType\Exception\StrongTypeException;
use StrongType\Float\Longitude;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class LongitudeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(float $value)
    {
        // When
        $longitude = new Longitude($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(float $value)
    {
        // Given
        $longitude = new Longitude($value);

        // When
        $obtainedValue = $longitude->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(float $value)
    {
        // Given
        $longitude = new Longitude($value);

        // When
        $debugInfo = $longitude->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [0.0],
            [180.0],
            [-180.0],
            [90.0],
            [-90.0],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(float $value, string $expected)
    {
        // Given
        $longitude = new Longitude($value);

        // When
        $stringRepresentation = (string) $longitude;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(float $value, string $expected)
    {
        // Given
        $longitude = new Longitude($value);

        // When
        $jsonSerialization = \json_encode($longitude);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0.0, '0'],
            [180.0, '180'],
            [-180.0, '-180'],
            [90.0, '90'],
            [-90.0, '-90'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(float $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $longitude = new Longitude($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [180.1],
            [-180.1],
            [360.0],
            [-360.0],
        ];
    }
}
