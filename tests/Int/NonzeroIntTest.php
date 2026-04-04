<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\NonzeroInt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class NonzeroIntTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testValidValue(int $value)
    {
        // When
        $nonzeroInt = new NonzeroInt($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testGetValue(int $value)
    {
        // Given
        $nonzeroInt = new NonzeroInt($value);

        // When
        $obtainedValue = $nonzeroInt->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValues')]
    public function testDebugInfo(int $value)
    {
        // Given
        $nonzeroInt = new NonzeroInt($value);

        // When
        $debugInfo = $nonzeroInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [\PHP_INT_MIN],
            [-8973298792],
            [-1000],
            [-10],
            [-3],
            [-2],
            [-1],
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
        $nonzeroInt = new NonzeroInt($value);

        // When
        $stringRepresentation = (string) $nonzeroInt;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    #[Test]
    #[DataProvider('dataProviderForValidValuesStringRepresentation')]
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $nonzeroInt = new NonzeroInt($value);

        // When
        $jsonSerialization = \json_encode($nonzeroInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [\PHP_INT_MIN, \strval(\PHP_INT_MIN)],
            [-8973298792, '-8973298792'],
            [-1000, '-1000'],
            [-10, '-10'],
            [-3, '-3'],
            [-2, '-2'],
            [-1, '-1'],
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
        $nonzeroInt = new NonzeroInt($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [0],
        ];
    }
}
