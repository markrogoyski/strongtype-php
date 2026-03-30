<?php

declare(strict_types=1);

namespace StrongType\Tests\String;

use StrongType\Exception\StrongTypeException;
use StrongType\String\Base64String;

class Base64StringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test         Valid value
     * @dataProvider dataProviderForValidValues
     * @param        string $value
     */
    public function testValidValue(string $value)
    {
        // When
        $base64String = new Base64String($value);

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
        $base64String = new Base64String($value);

        // When
        $obtainedValue = $base64String->getValue();

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
        $base64String = new Base64String($value);

        // When
        $debugInfo = $base64String->__debugInfo();

        // Then
        $this->assertSame($value, $debugInfo['value']);
    }

    public function dataProviderForValidValues(): array
    {
        return [
            ['aGVsbG8='],
            ['dGVzdA=='],
            ['YWJj'],
            ['AQID'],
            ['dGVzdGluZzEyMw=='],
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
        $base64String = new Base64String($value);

        // When
        $stringRepresentation = (string) $base64String;

        // Then
        $this->assertSame($expected, $stringRepresentation);
    }

    public function dataProviderForValidValuesStringRepresentation(): array
    {
        return [
            ['aGVsbG8=', 'aGVsbG8='],
            ['dGVzdA==', 'dGVzdA=='],
            ['YWJj', 'YWJj'],
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
        $base64String = new Base64String($value);

        // When
        $jsonSerialization = json_encode($base64String);

        // Then
        $this->assertSame($expected, $jsonSerialization);
    }

    public function dataProviderForValidValuesJsonStringRepresentation(): array
    {
        return [
            ['aGVsbG8=', '"aGVsbG8="'],
            ['dGVzdA==', '"dGVzdA=="'],
            ['YWJj', '"YWJj"'],
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
        $base64String = new Base64String($value);
    }

    public function dataProviderForInvalidValues(): array
    {
        return [
            [''],
            ['not base64!'],
            ['hello world'],
            ['abc@def'],
            ['==='],
            ['aGVsbG8=extra!'],
        ];
    }
}
