<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\NegativeInt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class NegativeIntTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $negativeInt = new NegativeInt($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $negativeInt = new NegativeInt($value);

        // When
        $obtainedValue = $negativeInt->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $negativeInt = new NegativeInt($value);

        // When
        $debugInfo = $negativeInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [-1],
            [-2],
            [-3],
            [-10],
            [-1000],
            [-8973298792],
            [\PHP_INT_MIN],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $negativeInt = new NegativeInt($value);

        // When
        $stringRepresentation = (string) $negativeInt;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $negativeInt = new NegativeInt($value);

        // When
        $jsonSerialization = \json_encode($negativeInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [-1, '-1'],
            [-2, '-2'],
            [-3, '-3'],
            [-10, '-10'],
            [-1000, '-1000'],
            [-8973298792, '-8973298792'],
            [\PHP_INT_MIN, \strval(\PHP_INT_MIN)],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForInvalidValues')]
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $negativeInt = new NegativeInt($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [0],
            [1],
            [2],
            [10],
            [1000],
            [8973298792],
            [\PHP_INT_MAX],
        ];
    }
}
