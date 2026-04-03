<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\OddInt;

class OddIntTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testValidValue(int $value)
    {
        // When
        $oddInt = new OddInt($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test         Get value
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testGetValue(int $value)
    {
        // Given
        $oddInt = new OddInt($value);

        // When
        $obtainedValue = $oddInt->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testDebugInfo(int $value)
    {
        // Given
        $oddInt = new OddInt($value);

        // When
        $debugInfo = $oddInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [1],
            [3],
            [-1],
            [-3],
            [11],
            [1001],
            [\PHP_INT_MAX],
        ];
    }

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        int    $value
     * @param        string $expected
     */
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $oddInt = new OddInt($value);

        // When
        $stringRepresentation = (string) $oddInt;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * @test         JSON serialization
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        int    $value
     * @param        string $expected
     */
    public function testJsonSerialization(int $value, string $expected)
    {
        // Given
        $oddInt = new OddInt($value);

        // When
        $jsonSerialization = \json_encode($oddInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [1, '1'],
            [3, '3'],
            [-1, '-1'],
            [-3, '-3'],
            [11, '11'],
            [1001, '1001'],
            [\PHP_INT_MAX, \strval(\PHP_INT_MAX)],
        ];
    }

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        int $value
     */
    public function testInvalidValue(int $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $oddInt = new OddInt($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [0],
            [2],
            [4],
            [-2],
            [-4],
            [10],
            [1000],
        ];
    }
}
