<?php

declare(strict_types=1);

namespace StrongType\Tests\Bool;

use StrongType\Exception\StrongTypeException;
use StrongType\Bool\FalseValue;

class FalseValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        bool $value
     */
    public function testValidValue(bool $value)
    {
        // When
        $falseValue = new FalseValue($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test         Get value
     * @dataProvider dataProviderForValidValues
     * @param        bool $value
     */
    public function testGetValue(bool $value)
    {
        // Given
        $falseValue = new FalseValue($value);

        // When
        $obtainedValue = $falseValue->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        bool $value
     */
    public function testDebugInfo(bool $value)
    {
        // Given
        $falseValue = new FalseValue($value);

        // When
        $debugInfo = $falseValue->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [false],
        ];
    }

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        bool   $value
     * @param        string $expected
     */
    public function testStringRepresentation(bool $value, string $expected)
    {
        // Given
        $falseValue = new FalseValue($value);

        // When
        $stringRepresentation = (string) $falseValue;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    /**
     * @test         JSON serialization
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        bool   $value
     * @param        string $expected
     */
    public function testJsonSerialization(bool $value, string $expected)
    {
        // Given
        $falseValue = new FalseValue($value);

        // When
        $jsonSerialization = \json_encode($falseValue);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [false, 'false'],
        ];
    }

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        bool $value
     */
    public function testInvalidValue(bool $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $falseValue = new FalseValue($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [true],
        ];
    }
}
