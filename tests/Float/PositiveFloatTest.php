<?php

declare(strict_types=1);

namespace StrongType\Tests\Float;

use StrongType\Exception\StrongTypeException;
use StrongType\Float\PositiveFloat;

class PositiveFloatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        float $value
     */
    public function testValidValue(float $value)
    {
        // When
        $nonnegativeFloat = new PositiveFloat($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test         Get value
     * @dataProvider dataProviderForValidValues
     * @param        float $value
     */
    public function testGetValue(float $value)
    {
        // Given
        $nonnegativeFloat = new PositiveFloat($value);

        // When
        $obtainedValue = $nonnegativeFloat->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        float $value
     */
    public function testDebugInfo(float $value)
    {
        // Given
        $nonnegativeFloat = new PositiveFloat($value);

        // When
        $debugInfo = $nonnegativeFloat->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            [1.0],
            [2.2],
            [3.945],
            [10.1],
            [1000.005],
            [8973298792.777],
            [\PHP_FLOAT_MIN],
            [\PHP_FLOAT_MAX],
        ];
    }

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        float  $value
     * @param        string $expected
     */
    public function testStringRepresentation(float $value, string $expected)
    {
        // Given
        $nonnegativeFloat = new PositiveFloat($value);

        // When
        $stringRepresentation = (string) $nonnegativeFloat;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * @test         JSON serialization
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        float  $value
     * @param        string $expected
     */
    public function testJsonSerialization(float $value, string $expected)
    {
        // Given
        $nonnegativeFloat = new PositiveFloat($value);

        // When
        $jsonSerialization = json_encode($nonnegativeFloat);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [1.0, '1'],
            [2.2, '2.2'],
            [3.945, '3.945'],
            [10.1, '10.1'],
            [1000.005, '1000.005'],
            [8973298792.777, '8973298792.777'],
        ];
    }

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        float $value
     */
    public function testInvalidValue(float $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $nonnegativeFloat = new PositiveFloat($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [0.0],
            [-1.0],
            [-2.2],
            [-10.1],
            [-1000.005],
            [-8973298792.777],
            [-\PHP_FLOAT_MIN],
            [-\PHP_FLOAT_MAX],
        ];
    }
}
