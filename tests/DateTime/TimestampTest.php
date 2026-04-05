<?php

declare(strict_types=1);

namespace StrongType\Tests\DateTime;

use StrongType\Exception\StrongTypeException;
use StrongType\DateTime\Timestamp;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class TimestampTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $timestamp = new Timestamp($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $timestamp = new Timestamp($value);

        // When
        $obtainedValue = $timestamp->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $timestamp = new Timestamp($value);

        // When
        $debugInfo = $timestamp->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [0],
            [1],
            [1000000],
            [1700000000],
            [\PHP_INT_MAX],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $timestamp = new Timestamp($value);

        // When
        $stringRepresentation = (string) $timestamp;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $timestamp = new Timestamp($value);

        // When
        $jsonSerialization = \json_encode($timestamp);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [0, '0'],
            [1, '1'],
            [1000000, '1000000'],
            [1700000000, '1700000000'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $timestamp = new Timestamp($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [-1],
            [-1000],
            [\PHP_INT_MIN],
        ];
    }
}
