<?php

declare(strict_types=1);

namespace StrongType\Tests\Int;

use StrongType\Exception\StrongTypeException;
use StrongType\Int\NonzeroInt;

class NonzeroIntTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        int $value
     */
    public function testValidValue(int $value)
    {
        // When
        $nonzeroInt = new NonzeroInt($value);

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
        $nonzeroInt = new NonzeroInt($value);

        // When
        $obtainedValue = $nonzeroInt->getValue();

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
        $nonzeroInt = new NonzeroInt($value);

        // When
        $debugInfo = $nonzeroInt->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
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

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        int    $value
     * @param        string $expected
     */
    public function testStringRepresentation(int $value, string $expected)
    {
        // Given
        $nonzeroInt = new NonzeroInt($value);

        // When
        $stringRepresentation = (string) $nonzeroInt;

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
        $nonzeroInt = new NonzeroInt($value);

        // When
        $jsonSerialization = json_encode($nonzeroInt);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
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
        $nonzeroInt = new NonzeroInt($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [0],
        ];
    }
}
