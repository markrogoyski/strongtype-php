<?php

declare(strict_types=1);

namespace StrongType\Tests\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\DateTime\DateString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DateStringTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(string $value)
    {
        // When
        $dateString = new DateString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(string $value)
    {
        // Given
        $dateString = new DateString($value);

        // When
        $obtainedValue = $dateString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(string $value)
    {
        // Given
        $dateString = new DateString($value);

        // When
        $debugInfo = $dateString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['2024-01-01'],
            ['2024-12-31'],
            ['2024-02-29'],
            ['2000-06-15'],
            ['1999-01-01'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $dateString = new DateString($value);

        // When
        $stringRepresentation = (string) $dateString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesJsonStringRepresentation')]
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $dateString = new DateString($value);

        // When
        $jsonSerialization = \json_encode($dateString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['2024-01-01', '2024-01-01'],
            ['2024-12-31', '2024-12-31'],
        ];
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['2024-01-01', '"2024-01-01"'],
            ['2024-12-31', '"2024-12-31"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $dateString = new DateString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['2024-13-01'],
            ['2024-00-01'],
            ['2024-01-32'],
            ['2023-02-29'],
            ['not-a-date'],
            ['01-01-2024'],
            ['2024/01/01'],
            ['20240101'],
        ];
    }
}
