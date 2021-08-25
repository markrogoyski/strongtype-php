<?php

declare(strict_types=1);

namespace StrongType\Tests\Integer;

use StrongType\Exception\StrongTypeException;
use StrongType\Integer\NegativeInt;

class NegativeIntTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testValidValue(int $value)
    {
        // When
        $negativeInt = new NegativeInt($value);

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
        $negativeInt = new NegativeInt($value);

        // When
        $obtainedValue = $negativeInt->getValue();

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
        $negativeInt = new NegativeInt($value);

        // When
        $debugInfo = $negativeInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
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

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        int    $value
     * @param        string $expected
     */
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $negativeInt = new NegativeInt($value);

        // When
        $stringRepresentation = (string) $negativeInt;

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
        $negativeInt = new NegativeInt($value);

        // When
        $jsonSerialization = json_encode($negativeInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
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
        $negativeInt = new NegativeInt($value);
    }

    public function dataProviderForInvalidValues(): array
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
