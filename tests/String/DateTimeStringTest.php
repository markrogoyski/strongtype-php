<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\DateTimeString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DateTimeStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $dateTimeString = new DateTimeString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $dateTimeString = new DateTimeString($value);

        // When
        $obtainedValue = $dateTimeString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $dateTimeString = new DateTimeString($value);

        // When
        $debugInfo = $dateTimeString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['2024-01-01'],
            ['2024-01-01T12:00:00'],
            ['2024-01-01T12:00:00+00:00'],
            ['2024-12-31T23:59:59Z'],
            ['January 1, 2024'],
            ['now'],
            ['yesterday'],
            ['tomorrow'],
            ['+1 day'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $dateTimeString = new DateTimeString($value);

        // When
        $stringRepresentation = (string) $dateTimeString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $dateTimeString = new DateTimeString($value);

        // When
        $jsonSerialization = \json_encode($dateTimeString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['2024-01-01', '2024-01-01'],
            ['2024-01-01T12:00:00', '2024-01-01T12:00:00'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['2024-01-01', '"2024-01-01"'],
            ['2024-01-01T12:00:00', '"2024-01-01T12:00:00"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $dateTimeString = new DateTimeString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['not a date'],
            ['abc123'],
            ['99/99/9999'],
        ];
    }
}
