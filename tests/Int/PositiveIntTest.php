<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\PositiveInt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PositiveIntTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $positiveInt = new PositiveInt($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $positiveInt = new PositiveInt($value);

        // When
        $obtainedValue = $positiveInt->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $positiveInt = new PositiveInt($value);

        // When
        $debugInfo = $positiveInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [1],
            [2],
            [3],
            [10],
            [1000],
            [8973298792],
            [\PHP_INT_MAX],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $positiveInt = new PositiveInt($value);

        // When
        $stringRepresentation = (string) $positiveInt;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $positiveInt = new PositiveInt($value);

        // When
        $jsonSerialization = \json_encode($positiveInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [1, '1'],
            [2, '2'],
            [3, '3'],
            [10, '10'],
            [1000, '1000'],
            [8973298792, '8973298792'],
            [\PHP_INT_MAX, \strval(\PHP_INT_MAX)],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $positiveInt = new PositiveInt($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [0],
            [-1],
            [-2],
            [-10],
            [-1000],
            [-8973298792],
            [\PHP_INT_MIN],
        ];
    }
}
