<?php

declare(strict_types=1);

namespace StrongType\Tests\Integer;

use StrongType\Exception\StrongTypeException;
use StrongType\Integer\PositiveInt;

class PositiveIntTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testValidValue(int $value)
    {
        // When
        $positiveInt = new PositiveInt($value);

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
        $positiveInt = new PositiveInt($value);

        // When
        $obtainedValue = $positiveInt->getValue();

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
        $positiveInt = new PositiveInt($value);

        // When
        $debugInfo = $positiveInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
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

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        int    $value
     * @param        string $expected
     */
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $positiveInt = new PositiveInt($value);

        // When
        $stringRepresentation = (string) $positiveInt;

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
        $positiveInt = new PositiveInt($value);

        // When
        $jsonSerialization = json_encode($positiveInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
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
        $positiveInt = new PositiveInt($value);
    }

    public function dataProviderForInvalidValues(): array
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
