<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\UuidString;

class UuidStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $uuidString = new UuidString($value);

        // Then
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test         Get value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testGetValue(string $value)
    {
        // Given
        $uuidString = new UuidString($value);

        // When
        $obtainedValue = $uuidString->getValue();

        // Then
        $this->assertSame($value, $obtainedValue);
    }

    /**
     * @test         Debug info
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testDebugInfo(string $value)
    {
        // Given
        $uuidString = new UuidString($value);

        // When
        $debugInfo = $uuidString->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public static function dataProviderForValidValues(): array
    {
        return [
            ['550e8400-e29b-41d4-a716-446655440000'],
            ['6ba7b810-9dad-11d1-80b4-00c04fd430c8'],
            ['f47ac10b-58cc-4372-a567-0e02b2c3d479'],
            ['00000000-0000-0000-0000-000000000000'],
        ];
    }

    /**
     * @test         String representation
     * @dataProvider dataProviderForValidValuesStringRepresentation
     * @param        string $value
     * @param        string $expected
     */
    public function testStringRepresentation(string $value, string $expected)
    {
        // Given
        $uuidString = new UuidString($value);

        // When
        $stringRepresentation = (string) $uuidString;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public static function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['550e8400-e29b-41d4-a716-446655440000', '550e8400-e29b-41d4-a716-446655440000'],
            ['6ba7b810-9dad-11d1-80b4-00c04fd430c8', '6ba7b810-9dad-11d1-80b4-00c04fd430c8'],
            ['f47ac10b-58cc-4372-a567-0e02b2c3d479', 'f47ac10b-58cc-4372-a567-0e02b2c3d479'],
        ];
    }

    /**
     * @test         JSON serialization
     * @dataProvider dataProviderForValidValuesJsonStringRepresentation
     * @param        string $value
     * @param        string $expected
     */
    public function testJsonSerialization(string $value, string $expected)
    {
        // Given
        $uuidString = new UuidString($value);

        // When
        $jsonSerialization = \json_encode($uuidString);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public static function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['550e8400-e29b-41d4-a716-446655440000', '"550e8400-e29b-41d4-a716-446655440000"'],
            ['6ba7b810-9dad-11d1-80b4-00c04fd430c8', '"6ba7b810-9dad-11d1-80b4-00c04fd430c8"'],
            ['f47ac10b-58cc-4372-a567-0e02b2c3d479', '"f47ac10b-58cc-4372-a567-0e02b2c3d479"'],
        ];
    }

    /**
     * @test         Invalid value
     * @dataProvider dataProviderForInvalidValues
     * @param        string $value
     */
    public function testInvalidValue(string $value)
    {
        // Then
        $this->expectException(StrongTypeException::class);

        // When
        $uuidString = new UuidString($value);
    }

    public static function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['not-a-uuid'],
            ['550e8400e29b41d4a716446655440000'],
            ['550e8400-e29b-41d4-a716-44665544000'],
            ['ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
        ];
    }
}
