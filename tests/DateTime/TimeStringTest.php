<?php

declare(strict_types=1);

namespace StrongType\Tests\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\DateTime\TimeString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class TimeStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $timeString = new TimeString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $timeString = new TimeString($value);

        // When
        $obtainedValue = $timeString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $timeString = new TimeString($value);

        // When
        $debugInfo = $timeString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['00:00:00'],
            ['12:00:00'],
            ['23:59:59'],
            ['01:30:45'],
            ['15:15:15'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $timeString = new TimeString($value);

        // When
        $stringRepresentation = (string) $timeString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $timeString = new TimeString($value);

        // When
        $jsonSerialization = \json_encode($timeString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['00:00:00', '00:00:00'],
            ['12:00:00', '12:00:00'],
            ['23:59:59', '23:59:59'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['00:00:00', '"00:00:00"'],
            ['12:00:00', '"12:00:00"'],
            ['23:59:59', '"23:59:59"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $timeString = new TimeString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['24:00:00'],
            ['12:60:00'],
            ['12:00:60'],
            ['12:00'],
            ['noon'],
            ['1:00:00'],
            ['12:0:00'],
            ['12:00:0'],
        ];
    }
}
