<?php

declare(strict_types=1);

namespace StrongType\Tests\Bool;

use StrongType\Exception\StrongTypeException;
use StrongType\Bool\TrueValue;

class TrueValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        bool $value
     */
    public function testValidValue(bool $value)
    {
        // When
        $trueValue = new TrueValue($value);

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
        $trueValue = new TrueValue($value);

        // When
        $obtainedValue = $trueValue->getValue();

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
        $trueValue = new TrueValue($value);

        // When
        $debugInfo = $trueValue->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            [true],
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
        $trueValue = new TrueValue($value);

        // When
        $stringRepresentation = (string) $trueValue;

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
        $trueValue = new TrueValue($value);

        // When
        $jsonSerialization = \json_encode($trueValue);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            [true, 'true'],
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
        $this->expectException(\TypeError::class);

        // When
        $trueValue = new TrueValue($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [false],
        ];
    }
}
